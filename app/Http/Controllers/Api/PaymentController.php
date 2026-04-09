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
     * Verify payment status after Stripe Elements redirect.
     * Called by the Next.js success page with payment_intent + payment_gateway in the body.
     */
    public function verify(Request $request, $order_id)
    {
        $request->validate([
            'payment_gateway' => 'required|string',
        ]);

        try {
            $order   = Order::findOrFail($order_id);
            $gateway = $request->get('payment_gateway');

            // If already paid (webhook beat us to it) — return success immediately.
            if ($order->payment_status === \App\Enums\PaymentStatus::PAID) {
                return $this->successResponse(['order_id' => $order->id, 'status' => 'paid'], 'Payment already confirmed');
            }

            $result = $this->paymentManagerService->gateway($gateway)->success($order, $request);

            // success() returns a RedirectResponse — inspect the target URL to determine outcome.
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                $targetUrl = $result->getTargetUrl();
                if (str_contains($targetUrl, 'successful') || str_contains($targetUrl, 'success')) {
                    return $this->successResponse(['order_id' => $order->id, 'status' => 'paid'], 'Payment confirmed');
                }
                return $this->errorResponse('Payment could not be confirmed', 422);
            }

            return $this->successResponse($result, 'Payment verification complete');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
