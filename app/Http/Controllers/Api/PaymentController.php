<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\PaymentManagerService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    use ApiResponse;

    private PaymentManagerService $paymentManagerService;

    public function __construct(PaymentManagerService $paymentManagerService)
    {
        $this->paymentManagerService = $paymentManagerService;
    }

    /**
     * Initiate payment for an order.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'order_id'        => 'required|exists:orders,id',
            'payment_gateway' => 'required|string',
        ]);

        try {
            $order = Order::findOrFail($request->order_id);
            $gateway = $request->payment_gateway;
            
            if ($this->paymentManagerService->gateway($gateway)->status()) {
                $response = $this->paymentManagerService->gateway($gateway)->payment($order, $request);
                
                if ($response instanceof \Illuminate\Http\RedirectResponse) {
                    return $this->successResponse(['redirect_url' => $response->getTargetUrl()], 'Payment initiated');
                }

                return $this->successResponse($response, 'Payment initiated');
            }

            return $this->errorResponse('Payment gateway disabled', 422);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Verify payment status.
     */
    public function verify(Request $request, $order_id)
    {
        try {
            $order = Order::findOrFail($order_id);
            $gateway = $request->get('payment_gateway');
            
            $response = $this->paymentManagerService->gateway($gateway)->success($order, $request);

            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                $targetUrl = $response->getTargetUrl();
                $status = str_contains($targetUrl, 'success') ? 'success' : 'failed';
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                $redirectUrl = "{$frontendUrl}/payment/{$status}?order_id={$order->id}";
                
                return $this->successResponse(['redirect_url' => $redirectUrl, 'status' => $status], 'Payment verification complete');
            }

            return $this->successResponse($response, 'Payment verification response');

            return $this->successResponse($response, 'Payment verification response');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
