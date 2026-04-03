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
            $reason = $request->reason ?? 'Cancelled by customer';

            if ($order->status == OrderStatus::CANCELED) {
                return $this->errorResponse('Order is already cancelled', 422);
            }

            if ($order->status == OrderStatus::DELIVERED) {
                return $this->errorResponse('Delivered orders cannot be cancelled', 422);
            }

            if ($order->status == OrderStatus::PENDING) {
                // Immediate cancellation for pending orders
                $requestData = [
                    'status' => OrderStatus::CANCELED,
                    'reason' => $reason,
                ];
                $this->frontendOrderService->changeStatus($order, new \App\Http\Requests\OrderStatusRequest($requestData));

                return $this->successResponse([
                    'status' => true,
                    'type'   => 'immediate'
                ], 'Order cancelled successfully');
            }

            if ($order->status == OrderStatus::CONFIRMED || $order->status == OrderStatus::ON_THE_WAY) {
                // Cancellation request for confirmed/on the way orders
                // 1. Log a message in the order thread
                OrderMessage::create([
                    'order_id'    => $order->id,
                    'user_id'     => Auth::id(),
                    'sender_type' => 'customer',
                    'message'     => "[CANCELLATION REQUEST] " . $reason,
                    'is_read'     => false,
                ]);

                // 2. Mark as unviewed for admin and flag it
                $order->admin_viewed_at = null;
                $payload = $order->reasonPayload();
                $payload['cancellation_requested'] = true;
                $payload['customer_note'] = $reason;
                $order->reason = json_encode($payload);
                $order->save();

                // 3. Log Audit
                \App\Services\AuditLogger::cancellationRequested($order, $reason);

                // 4. Notify Admin
                try {
                    $orderMailNotificationBuilderService = new \App\Services\OrderMailNotificationBuilder($order->id);
                    $orderMailNotificationBuilderService->adminOrderCancellationNotification();
                } catch (Exception $e) {
                    \Illuminate\Support\Facades\Log::info("Cancellation Notification Error: " . $e->getMessage());
                }

                return $this->successResponse([
                    'status' => true,
                    'type'   => 'requested'
                ], 'Cancellation request submitted');
            }

            return $this->errorResponse('Order cannot be cancelled at this stage', 422);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage() ?: 'Order not found', 404);
        }
    }
}
