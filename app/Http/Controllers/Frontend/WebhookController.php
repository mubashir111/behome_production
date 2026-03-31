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
            Log::warning('Stripe webhook: STRIPE_WEBHOOK_SECRET not set — skipping signature check');
            // Still process in dev; block in production by checking APP_ENV
            if (app()->environment('production')) {
                return response()->json(['error' => 'Webhook secret not configured'], 500);
            }
            $event = json_decode($payload);
        } else {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::warning('Stripe webhook: invalid signature — ' . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 400);
            } catch (Exception $e) {
                Log::warning('Stripe webhook: parse error — ' . $e->getMessage());
                return response()->json(['error' => 'Invalid payload'], 400);
            }
        }

        // ── 2. Only act on charge.succeeded ─────────────────────────────────
        if ($event->type !== 'charge.succeeded') {
            return response()->json(['received' => true]);
        }

        $charge             = $event->data->object;
        $balanceTransaction = $charge->balance_transaction ?? null;

        if (!$balanceTransaction) {
            // balance_transaction is occasionally null on very first settlement tick;
            // Stripe will retry — return 200 to avoid retry noise, log for visibility.
            Log::info("Stripe webhook: charge.succeeded for {$charge->id} has no balance_transaction yet");
            return response()->json(['received' => true]);
        }

        // ── 3. Idempotent order update ───────────────────────────────────────
        try {
            DB::transaction(function () use ($charge, $balanceTransaction) {
                $capture = CapturePaymentNotification::where('token', $balanceTransaction)->first();

                if (!$capture) {
                    // Either the redirect handler already processed it (and deleted the record),
                    // or this charge belongs to a different integration. Both are fine.
                    return;
                }

                $order = Order::find($capture->order_id);

                if (!$order || $order->payment_status === PaymentStatus::PAID) {
                    // Already paid (redirect beat the webhook). Clean up and return.
                    $capture->delete();
                    return;
                }

                // Payment not yet recorded — process it now.
                (new PaymentService())->payment($order, 'stripe', $balanceTransaction);
                $capture->delete();

                Log::info("Stripe webhook: order #{$order->id} marked PAID via charge {$charge->id}");
            });
        } catch (Exception $e) {
            Log::error('Stripe webhook: processing error — ' . $e->getMessage());
            // Return 500 so Stripe retries the event (up to 3 days).
            return response()->json(['error' => 'Processing failed'], 500);
        }

        return response()->json(['received' => true]);
    }
}
