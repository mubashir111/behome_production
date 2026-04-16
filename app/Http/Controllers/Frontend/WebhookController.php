<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\PaymentStatus;
use App\Models\CapturePaymentNotification;
use App\Models\Order;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Stripe webhook endpoint.
     *
     * Register in Stripe Dashboard → Developers → Webhooks:
     *   URL:    https://yourdomain.com/api/webhooks/stripe
     *   Events: charge.succeeded
     *
     * Copy the "Signing secret" to STRIPE_WEBHOOK_SECRET in .env.
     */
    public function stripe(Request $request): \Illuminate\Http\JsonResponse
    {
        $secret    = config('services.stripe.webhook_secret');
        $signature = $request->header('Stripe-Signature');
        $payload   = $request->getContent(); // raw body — must NOT be decoded first

        // ── 1. Verify signature ──────────────────────────────────────────────
        if (!$secret) {
            Log::error('Stripe webhook: STRIPE_WEBHOOK_SECRET not set — rejecting request. Set STRIPE_WEBHOOK_SECRET in .env.');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature — ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (Exception $e) {
            Log::warning('Stripe webhook: parse error — ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // ── 2. Handle events ───────────────────────────────────────────────
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $orderId = $paymentIntent->metadata->order_id ?? null;

            if (!$orderId) {
                Log::warning('Stripe webhook: payment_intent.succeeded missing order_id in metadata');
                return response()->json(['received' => true]);
            }

            try {
                DB::transaction(function () use ($orderId, $paymentIntent) {
                    $order = Order::find($orderId);

                    if (!$order || $order->payment_status === PaymentStatus::PAID) {
                        return;
                    }

                    // Mark as PAID
                    (new PaymentService())->payment($order, 'stripe', $paymentIntent->id);
                    Log::info("Stripe webhook: order #{$order->id} marked PAID via PaymentIntent {$paymentIntent->id}");
                });
            } catch (Exception $e) {
                Log::error('Stripe webhook error: ' . $e->getMessage());
                return response()->json(['error' => 'Processing failed'], 500);
            }
        } elseif ($event->type === 'charge.succeeded') {
            // Legacy/Fallback support
            $charge = $event->data->object;
            if ($charge->payment_intent) {
                 // Already handled by payment_intent.succeeded if it's modern
                 return response()->json(['received' => true]);
            }
            
            $balanceTransaction = $charge->balance_transaction ?? null;
            if ($balanceTransaction) {
                DB::transaction(function () use ($charge, $balanceTransaction) {
                    $capture = CapturePaymentNotification::where('token', $balanceTransaction)->first();
                    if ($capture) {
                        $order = Order::find($capture->order_id);
                        if ($order && $order->payment_status !== PaymentStatus::PAID) {
                            (new PaymentService())->payment($order, 'stripe', $balanceTransaction);
                            $capture->delete();
                        }
                    }
                });
            }
        }

        return response()->json(['received' => true]);
    }
}
