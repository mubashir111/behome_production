<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Services\OrderService;
use App\Http\Requests\PaginateRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $currencySymbol = config('app.currency_symbol');
        $search         = trim($request->get('search', ''));
        $status         = $request->get('status');
        $paymentStatus  = $request->get('payment_status');

        $query = \App\Models\Order::with(['user', 'orderProducts'])
            ->where('order_type', '!=', \App\Enums\OrderType::POS)
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_serial_no', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        if ($paymentStatus !== null && $paymentStatus !== '') {
            $query->where('payment_status', (int) $paymentStatus);
        }

        $orders = $query->paginate(15)->appends($request->query());

        return view('admin.orders.index', compact('orders', 'currencySymbol', 'search'));
    }

    public function show(Order $order)
    {
        $order = $this->orderService->show($order->loadMissing(['user', 'address', 'outletAddress', 'orderProducts.product']));

        // Mark as viewed by admin (first time only)
        if (!$order->admin_viewed_at) {
            $order->updateQuietly(['admin_viewed_at' => now()]);
        }

        // Mark customer messages on this order as read
        $order->messages()->where('sender_type', 'customer')->where('is_read', false)->update(['is_read' => true]);

        $currencySymbol = config('app.currency_symbol');
        return view('admin.orders.show', compact('order', 'currencySymbol'));
    }

    public function update(OrderStatusRequest $request, Order $order)
    {
        try {
            $sendEmail = $request->has('send_email');
        $this->orderService->changeStatus($order, $request, false, $sendEmail);
            return back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        try {
            $this->orderService->destroy($order);
            return redirect()->route('admin.orders.index')->with('success', 'Order archived successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function archived()
    {
        $orders = Order::onlyTrashed()
            ->with(['user', 'transaction'])
            ->latest('deleted_at')
            ->paginate(20);
        $currencySymbol = config('app.currency_symbol');
        return view('admin.orders.archived', compact('orders', 'currencySymbol'));
    }

    public function restore($id)
    {
        try {
            $order = Order::onlyTrashed()->findOrFail($id);
            $this->orderService->restore($order);
            return redirect()->route('admin.orders.archived')->with('success', 'Order #' . $order->order_serial_no . ' restored successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $order = Order::onlyTrashed()->findOrFail($id);
            $this->orderService->forceDestroy($order);
            return redirect()->route('admin.orders.archived')->with('success', 'Order permanently deleted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reply(Request $request, Order $order)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $message = OrderMessage::create([
            'order_id'    => $order->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'sender_type' => 'admin',
        ]);

        if ($request->has('send_email') && $order->user && $order->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)->send(
                    new \App\Mail\OrderReplyMail($order, $request->message)
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send order reply email: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    public function updatePaymentStatus(PaymentStatusRequest $request, Order $order)
    {
        try {
            $this->orderService->changePaymentStatus($order, $request);
            return back()->with('success', 'Payment status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
