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

    public function index(PaginateRequest $request)
    {
        $request->merge(['paginate' => 1]);
        $orders = $this->orderService->list($request);
        $currencySymbol = env('CURRENCY_SYMBOL', '₹');
        $search = $request->get('search', '');
        if ($search) {
            $orders = \App\Models\Order::with(['user', 'transaction', 'orderProducts'])
                ->where(function ($q) use ($search) {
                    $q->where('order_serial_no', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                })
                ->latest()
                ->paginate(15)
                ->appends(['search' => $search]);
        }
        if ($request->get('status')) {
            $status = (int) $request->get('status');
            $orders = \App\Models\Order::with(['user', 'transaction', 'orderProducts'])
                ->where('status', $status)
                ->latest()
                ->paginate(15)
                ->appends(['status' => $status]);
        }
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

        $currencySymbol = env('CURRENCY_SYMBOL', '₹');
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
            return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
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
