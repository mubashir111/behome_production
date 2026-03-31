<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusRequest;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderActionController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Cancel or request cancellation of an order.
     * - PENDING: instant cancel (not yet processed)
     * - CONFIRMED: sends a cancellation request to admin for approval
     */
    public function cancel(Request $request, Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED])) {
            return response(['status' => false, 'message' => 'This order cannot be cancelled at its current status.'], 422);
        }

        $reason = $request->input('reason', 'Cancellation requested by customer.');

        // PENDING → instant cancel (order not yet processed)
        if ($order->status === OrderStatus::PENDING) {
            try {
                $statusRequest = OrderStatusRequest::create('', 'POST', [
                    'status' => OrderStatus::CANCELED,
                    'reason' => $reason,
                ]);
                $statusRequest->setContainer(app())->setRedirector(app('redirect'));
                $this->orderService->changeStatus($order, $statusRequest, true);

                return response(['status' => true, 'type' => 'cancelled', 'message' => 'Your order has been cancelled successfully.'], 200);
            } catch (\Exception $e) {
                return response(['status' => false, 'message' => $e->getMessage()], 422);
            }
        }

        // CONFIRMED → send cancellation request to admin (requires approval)
        $existing = $order->messages()
            ->where('sender_type', 'customer')
            ->where('message', 'like', '[CANCELLATION REQUEST]%')
            ->exists();

        if ($existing) {
            return response(['status' => false, 'message' => 'You have already submitted a cancellation request for this order. Please wait for admin approval.'], 422);
        }

        $order->messages()->create([
            'user_id'     => $user->id,
            'sender_type' => 'customer',
            'message'     => "[CANCELLATION REQUEST]\n" . $reason,
            'is_read'     => false,
        ]);

        return response([
            'status'  => true,
            'type'    => 'requested',
            'message' => 'Cancellation request submitted. Our team will review and respond within 24 hours.',
        ], 200);
    }

    /**
     * Get messages for an order.
     */
    public function messages(Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Mark admin messages as read when customer views
        $order->messages()->where('sender_type', 'admin')->where('is_read', false)->update(['is_read' => true]);

        $messages = $order->messages()->with('user:id,name')->orderBy('created_at')->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'sender_type' => $m->sender_type,
                'sender_name' => $m->sender_type === 'admin' ? 'Support Team' : ($m->user->name ?? 'You'),
                'message'     => $m->message,
                'created_at'  => $m->created_at->format('M d, Y H:i'),
                'is_mine'     => $m->sender_type === 'customer',
            ]);

        return response(['status' => true, 'data' => $messages], 200);
    }

    /**
     * Send a message about an order.
     */
    public function sendMessage(Request $request, Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate(['message' => 'required|string|max:2000']);

        $msg = $order->messages()->create([
            'user_id'     => $user->id,
            'sender_type' => 'customer',
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        return response([
            'status' => true,
            'data'   => [
                'id'          => $msg->id,
                'sender_type' => 'customer',
                'sender_name' => 'You',
                'message'     => $msg->message,
                'created_at'  => $msg->created_at->format('M d, Y H:i'),
                'is_mine'     => true,
            ],
            'message' => 'Message sent successfully.',
        ], 200);
    }
}
