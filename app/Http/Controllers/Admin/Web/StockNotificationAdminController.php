<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Mail\StockNotificationMail;
use App\Models\StockNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StockNotificationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = StockNotification::with('product')
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('notified', $request->status === 'notified');
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Grouped count per product for the summary cards
        $summary = StockNotification::with('product')
            ->selectRaw('product_id, COUNT(*) as total, SUM(notified=0) as pending')
            ->groupBy('product_id')
            ->get();

        return view('admin.stock-notifications.index', compact('notifications', 'summary'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|integer|exists:products,id',
            'custom_message' => 'nullable|string|max:1000',
        ]);

        $subscribers = StockNotification::with('product')
            ->where('product_id', $request->product_id)
            ->where('notified', false)
            ->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'No pending subscribers found for this product.');
        }

        $product    = $subscribers->first()->product;
        $productUrl = config('app.url') . '/product/' . $product->slug;
        $sent       = 0;

        foreach ($subscribers as $sub) {
            try {
                Mail::to($sub->email)->send(
                    new StockNotificationMail($product->name, $productUrl, $request->custom_message ?? '')
                );
                $sub->update(['notified' => true, 'notified_at' => now()]);
                $sent++;
            } catch (\Throwable) {
                // continue sending to other subscribers even if one fails
            }
        }

        return back()->with('success', "Notification sent to {$sent} subscriber(s) for \"{$product->name}\".");
    }

    public function destroy(StockNotification $stockNotification)
    {
        $stockNotification->delete();
        return back()->with('success', 'Subscriber removed.');
    }
}
