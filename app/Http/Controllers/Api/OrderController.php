<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FrontendOrderService;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderResource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
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
}
