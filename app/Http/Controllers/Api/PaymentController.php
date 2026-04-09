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
     * Verify a Stripe payment after Elements redirect.
     *
     * Called by the Next.js /payment/success page with:
     *   { payment_gateway, payment_intent, redirect_status }
     *
     * Uses config('services.stripe.secret') directly so it never depends
     * on whatever key the admin may have entered in the payment-gateways panel.
     */
    public function verify(Request $request, $order_id)
    {
        $request->validate([
            'payment_gateway' => 'required|string',
        ]);

        try {
            $order = Order::findOrFail($order_id);

            // Already paid (webhook may have beaten us here) — return success immediately.
            if ($order->payment_status === \App\Enums\PaymentStatus::PAID) {
                return $this->successResponse(['order_id' => $order->id, 'status' => 'paid'], 'Payment already confirmed');
            }

            $gateway = strtolower($request->get('payment_gateway'));

            // ── Stripe Elements flow ─────────────────────────────────────────
            if ($gateway === 'stripe') {
                $paymentIntentId = $request->get('payment_intent');
                $redirectStatus  = $request->get('redirect_status');

                if (!$paymentIntentId) {
                    return $this->errorResponse('Missing payment_intent parameter', 422);
                }

                if ($redirectStatus !== 'succeeded') {
                    return $this->errorResponse('Payment was not successful (status: ' . $redirectStatus . ')', 422);
                }

                // Use the .env secret key — never the admin panel DB value.
                $stripeSecret = config('services.stripe.secret');
                if (!$stripeSecret) {
                    \Illuminate\Support\Facades\Log::error('Stripe verify: STRIPE_SECRET not set in .env');
                    return $this->errorResponse('Stripe is not configured on the server', 500);
                }

                $stripe = new \Stripe\StripeClient($stripeSecret);

                try {
                    $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
                } catch (Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Stripe verify: retrieve failed — ' . $e->getMessage());
                    return $this->errorResponse('Could not verify payment with Stripe', 422);
                }

                // Confirm the PaymentIntent belongs to this order.
                if ((string)($paymentIntent->metadata->order_id ?? '') !== (string)$order->id) {
                    \Illuminate\Support\Facades\Log::warning("Stripe verify: PaymentIntent {$paymentIntentId} order_id mismatch (expected {$order->id})");
                    return $this->errorResponse('Payment intent does not match this order', 422);
                }

                if ($paymentIntent->status !== 'succeeded') {
                    return $this->errorResponse('Payment not completed (status: ' . $paymentIntent->status . ')', 422);
                }

                // Mark order as paid.
                (new \App\Services\PaymentService())->payment($order, 'stripe', $paymentIntent->id);

                return $this->successResponse(['order_id' => $order->id, 'status' => 'paid'], 'Payment confirmed');
            }

            // ── Other gateways — fall back to gateway success() handler ──────
            $result = $this->paymentManagerService->gateway($gateway)->success($order, $request);

            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                $targetUrl = $result->getTargetUrl();
                if (str_contains($targetUrl, 'successful') || str_contains($targetUrl, 'success')) {
                    return $this->successResponse(['order_id' => $order->id, 'status' => 'paid'], 'Payment confirmed');
                }
                return $this->errorResponse('Payment could not be confirmed', 422);
            }

            return $this->successResponse($result, 'Payment verification complete');

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment verify error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }
}
