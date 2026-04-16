<?php

namespace App\Services;

use Exception;
use App\Enums\Ask;
use App\Models\User;
use App\Enums\Status;
use App\Models\Order;
use App\Models\Stock;
use App\Enums\OrderStatus;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
use App\Events\SendOrderSms;
use App\Events\SendOrderMail;
use App\Events\SendOrderPush;
use App\Events\SendOrderGotSms;
use App\Events\SendOrderGotMail;
use App\Events\SendOrderGotPush;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{

    public object $transaction;

    /**
     * @throws Exception
     */
    public function payment($order, $gatewaySlug, $transactionNo): object
    {
        try {
            // ── Core payment transaction ─────────────────────────────────────
            // Notifications are dispatched OUTSIDE the transaction so a mail/SMS/push
            // failure can never roll back a successful payment.
            DB::transaction(function () use ($order, $gatewaySlug, $transactionNo) {
                $transaction = Transaction::where(['order_id' => $order->id])->first();
                if (!$transaction) {
                    $transaction = Transaction::create([
                        'order_id'       => $order->id,
                        'transaction_no' => $transactionNo,
                        'amount'         => $order->total,
                        'payment_method' => $gatewaySlug,
                        'sign'           => '+',
                        'type'           => 'payment'
                    ]);
                }
                $this->transaction     = $transaction;
                $order->active         = Ask::YES;
                $order->payment_status = PaymentStatus::PAID;
                $order->save();
                Stock::where(['model_id' => $order->id, 'model_type' => Order::class, 'status' => Status::INACTIVE])?->update(['status' => Status::ACTIVE]);

                // Clear cart items for online payments
                $order->load('orderProducts');
                foreach ($order->orderProducts as $item) {
                    $q = \App\Models\Cart::where('user_id', $order->user_id)
                                        ->where('product_id', $item->product_id);
                    $item->variation_id > 0
                        ? $q->where('variation_id', $item->variation_id)
                        : $q->where(fn($w) => $w->whereNull('variation_id')->orWhere('variation_id', 0));
                    $q->delete();
                }
            });

            // ── Notifications — outside transaction ──────────────────────────
            // If any of these fail, the payment is already committed and safe.
            try {
                SendOrderMail::dispatch(['order_id' => $order->id, 'status' => OrderStatus::PENDING]);
                SendOrderSms::dispatch(['order_id' => $order->id, 'status' => OrderStatus::PENDING]);
                SendOrderPush::dispatch(['order_id' => $order->id, 'status' => OrderStatus::PENDING]);
                SendOrderGotMail::dispatch(['order_id' => $order->id]);
                SendOrderGotSms::dispatch(['order_id' => $order->id]);
                SendOrderGotPush::dispatch(['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::warning('PaymentService: notification dispatch failed — ' . $e->getMessage());
            }

            try {
                (new OrderMailNotificationBuilder($order->id))->adminPaymentReceivedNotification();
            } catch (\Exception $e) {
                Log::warning('PaymentService: admin notification failed — ' . $e->getMessage());
            }

            return $this->transaction;
        } catch (Exception $exception) {
            Log::error('PaymentService::payment failed — ' . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function cashBack($order, $gatewaySlug)
    {
        // Idempotency: skip if refund already issued for this order
        $existing = Transaction::where(['order_id' => $order->id, 'type' => 'cash_back'])->first();
        if ($existing) {
            return $existing;
        }

        // Only refund if a payment transaction exists for this order
        $paymentTransaction = Transaction::where(['order_id' => $order->id, 'type' => 'payment'])->first();
        if (!$paymentTransaction) {
            return null;
        }

        $refundTxNo      = $paymentTransaction->transaction_no . '_refund';
        $originalGateway = $paymentTransaction->payment_method; // e.g. 'stripe'

        // ── Stripe: issue real card refund via Stripe API ────────────────
        if ($originalGateway === 'stripe') {
            $stripeSecret = config('services.stripe.secret');
            if ($stripeSecret && $paymentTransaction->transaction_no) {
                try {
                    $stripe       = new \Stripe\StripeClient($stripeSecret);
                    $stripeRefund = $stripe->refunds->create([
                        'payment_intent' => $paymentTransaction->transaction_no,
                    ]);
                    $refundTxNo = $stripeRefund->id; // use Stripe refund ID as reference
                    Log::info("PaymentService::cashBack — Stripe refund issued: {$refundTxNo} for order {$order->id}");
                } catch (\Exception $e) {
                    Log::error("PaymentService::cashBack — Stripe refund failed for order {$order->id}: " . $e->getMessage());
                    // Fall through: record the cashback transaction as wallet credit so customer is not left empty-handed
                }
            }
            // For Stripe refunds: do NOT credit the wallet — money goes back to card
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $refundTxNo,
                'amount'         => $order->total,
                'payment_method' => 'stripe',
                'sign'           => '-',
                'type'           => 'cash_back'
            ]);
            return $transaction;
        }

        // ── Other gateways (COD, credit, PayPal, etc.): credit wallet ───
        // Both writes must succeed together — wrap in a transaction so a failed
        // save() cannot leave a transaction record without the matching balance update.
        $transaction = \Illuminate\Support\Facades\DB::transaction(function () use ($order, $refundTxNo, $gatewaySlug) {
            $transaction = Transaction::create([
                'order_id'       => $order->id,
                'transaction_no' => $refundTxNo,
                'amount'         => $order->total,
                'payment_method' => $gatewaySlug,
                'sign'           => '-',
                'type'           => 'cash_back'
            ]);

            $user = User::find($order->user_id);
            if (!$user) {
                Log::error("PaymentService::cashBack — user {$order->user_id} not found for order {$order->id}. Wallet not credited.");
                throw new \Exception("User not found — cannot credit wallet for order {$order->id}.");
            }
            $user->balance = ($user->balance + $order->total);
            $user->save();

            return $transaction;
        });

        return $transaction;
    }
}
