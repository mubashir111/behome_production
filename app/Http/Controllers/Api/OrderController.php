<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Services\FrontendOrderService;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Exception;

class OrderController extends Controller
{
    use ApiResponse;

    private FrontendOrderService $frontendOrderService;

    public function __construct(FrontendOrderService $frontendOrderService)
    {
        $this->frontendOrderService = $frontendOrderService;
    }

    public function index(PaginateRequest $request)
    {
        try {
            $orders = $this->frontendOrderService->myOrder($request);
            return $this->successResponse(OrderResource::collection($orders), 'Orders retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function store(OrderRequest $request)
    {
        try {
            $order = $this->frontendOrderService->myOrderStore($request);
            return $this->successResponse(new OrderDetailsResource($order), 'Order created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);
            return $this->successResponse(new OrderDetailsResource($order), 'Order details retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Order not found', 404);
        }
    }

    public function messages($id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);
            $userId = Auth::id();
            $messages = OrderMessage::where('order_id', $order->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'sender_type' => $m->sender_type,
                    'sender_name' => $m->sender_type === 'customer' ? 'You' : 'Support',
                    'is_mine'     => $m->sender_type === 'customer' && $m->user_id === $userId,
                    'created_at'  => $m->created_at->format('M d, Y H:i'),
                ]);

            OrderMessage::where('order_id', $order->id)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->successResponse($messages, 'Messages retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Order not found', 404);
        }
    }

    public function storeMessage(Request $request, $id)
    {
        try {
            $request->validate(['message' => 'required|string|max:2000']);
            $order = Order::where('user_id', Auth::id())->findOrFail($id);

            $message = OrderMessage::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'sender_type' => 'customer',
                'message'     => $request->message,
                'is_read'     => false,
            ]);

            return $this->successResponse([
                'id'          => $message->id,
                'message'     => $message->message,
                'sender_type' => $message->sender_type,
                'sender_name' => 'You',
                'is_mine'     => true,
                'created_at'  => $message->created_at->format('M d, Y H:i'),
            ], 'Message sent successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->errorResponse('Order not found', 404);
        }
    }
    public function cancel(Request $request, $id)
    {
        try {
            $order = Order::where('user_id', Auth::id())->findOrFail($id);
            $reason = $request->reason ?? 'Cancellation requested by customer.';

            if ($order->status == OrderStatus::CANCELED) {
                return $this->errorResponse('Order is already cancelled', 422);
            }

            if ($order->status == OrderStatus::DELIVERED) {
                return $this->errorResponse('Delivered orders cannot be cancelled', 422);
            }

            if (!in_array($order->status, [OrderStatus::PENDING, OrderStatus::CONFIRMED, OrderStatus::ON_THE_WAY])) {
                return $this->errorResponse('Order cannot be cancelled at this stage', 422);
            }

            // Check if there is already a pending cancellation request
            $existingRequest = $order->messages()
                ->where('sender_type', 'customer')
                ->where('message', 'like', '[CANCELLATION REQUEST]%')
                ->exists();

            if ($existingRequest) {
                return $this->errorResponse('You have already submitted a cancellation request for this order. Please wait for admin approval.', 422);
            }

            // Determine payment type
            $gateway   = \App\Models\PaymentGateway::find($order->payment_method);
            $gwSlug    = $gateway?->slug ?? '';
            $isOffline = in_array($gwSlug, ['cashondelivery', 'credit']);
            $isPaid    = $order->payment_status == PaymentStatus::PAID;

            // PENDING + offline/COD + not yet paid → instant cancel (no money involved)
            if ($order->status == OrderStatus::PENDING && $isOffline && !$isPaid) {
                $requestData = [
                    'status' => OrderStatus::CANCELED,
                    'reason' => $reason,
                ];
                $this->frontendOrderService->changeStatus($order, new \App\Http\Requests\OrderStatusRequest($requestData));

                return $this->successResponse([
                    'status' => true,
                    'type'   => 'cancelled',
                ], 'Your order has been cancelled successfully.');
            }

            // All other cases (online/Stripe paid, confirmed orders) → submit request to admin
            OrderMessage::create([
                'order_id'    => $order->id,
                'user_id'     => Auth::id(),
                'sender_type' => 'customer',
                'message'     => "[CANCELLATION REQUEST]\n" . $reason,
                'is_read'     => false,
            ]);

            $order->admin_viewed_at = null;
            $payload = $order->reasonPayload();
            $payload['cancellation_requested'] = true;
            $payload['customer_note'] = $reason;
            $order->reason = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $order->save();

            $refundNote = $isPaid
                ? ' If a refund is due, our team will process it back to your original payment method after review.'
                : '';

            return $this->successResponse([
                'status' => true,
                'type'   => 'requested',
            ], 'Cancellation request submitted. Our team will review and respond within 6–7 working days.' . $refundNote);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage() ?: 'Order not found', 404);
        }
    }
}
