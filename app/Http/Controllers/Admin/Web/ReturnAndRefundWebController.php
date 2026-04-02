<?php

namespace App\Http\Controllers\Admin\Web;

use App\Enums\RefundStatus;
use App\Enums\ReturnOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnAndRefund;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Smartisan\Settings\Facades\Settings;

class ReturnAndRefundWebController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnAndRefund::with(['user', 'returnReason', 'returnProducts'])->latest();

        if ($request->filled('search')) {
            $query->where('order_serial_no', 'like', '%' . $request->search . '%')
                ->orWhereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('refund_status')) {
            $query->where('refund_status', $request->refund_status);
        }

        $returns = $query->paginate(20)->withQueryString();
        return view('admin.returns.index', compact('returns'));
    }

    public function show(ReturnAndRefund $return)
    {
        $return->load(['user', 'returnReason', 'returnProducts.product', 'order']);

        if (!$return->admin_viewed_at) {
            $return->updateQuietly(['admin_viewed_at' => now()]);
        }

        return view('admin.returns.show', compact('return'));
    }

    /**
     * Accept or reject the return request.
     * Accepting sets refund_status = AWAITING_ITEM (no balance credit yet).
     * Rejecting closes the request with a reason.
     */
    public function changeStatus(Request $request, ReturnAndRefund $return)
    {
        $request->validate([
            'status' => 'required|integer',
            'reason' => 'nullable|string|max:700',
        ]);

        $newStatus = (int) $request->status;

        if ($newStatus === ReturnOrderStatus::REJECTED && !$request->filled('reason')) {
            return back()->withErrors(['reason' => 'A reason is required when rejecting a return.']);
        }

        $oldStatus = $return->status;
        $return->status = $newStatus;

        if ($newStatus === ReturnOrderStatus::ACCEPT) {
            // Start the refund pipeline — waiting for item to be shipped back
            $return->refund_status = RefundStatus::AWAITING_ITEM;
        } elseif ($newStatus === ReturnOrderStatus::REJECTED || $newStatus === ReturnOrderStatus::PENDING) {
            // Clear refund pipeline on reject or reset to pending
            $return->refund_status    = null;
            $return->refund_issued_at = null;
        }

        if ($request->filled('reason')) {
            $return->reject_reason = $request->reason;
        }

        $return->save();

        // Audit
        $order = Order::find($return->order_id);
        if ($order) {
            AuditLogger::returnStatusChanged($order, $oldStatus, $newStatus, $request->reason ?? null);
        }

        $labels = [
            ReturnOrderStatus::ACCEPT   => 'accepted — awaiting item shipment from customer',
            ReturnOrderStatus::REJECTED => 'rejected',
            ReturnOrderStatus::PENDING  => 'reset to pending',
        ];
        return back()->with('success', 'Return request ' . ($labels[$newStatus] ?? 'updated') . '.');
    }

    /**
     * Progress the refund pipeline:
     *   AWAITING_ITEM → ITEM_RECEIVED → REFUND_ISSUED (balance credited here)
     */
    public function processRefund(Request $request, ReturnAndRefund $return)
    {
        if ($return->status !== ReturnOrderStatus::ACCEPT) {
            return back()->withErrors(['refund' => 'Return must be accepted before processing the refund.']);
        }

        $request->validate(['refund_status' => 'required|integer']);
        $newRefundStatus = (int) $request->refund_status;

        // Only allow valid forward transitions
        $allowed = [
            RefundStatus::AWAITING_ITEM => [RefundStatus::ITEM_RECEIVED],
            RefundStatus::ITEM_RECEIVED => [RefundStatus::REFUND_ISSUED],
        ];

        $current = $return->refund_status ?? RefundStatus::AWAITING_ITEM;
        if (!in_array($newRefundStatus, $allowed[$current] ?? [])) {
            return back()->withErrors(['refund' => 'Invalid refund status transition.']);
        }

        $return->refund_status = $newRefundStatus;

        if ($newRefundStatus === RefundStatus::REFUND_ISSUED) {
            $return->refund_issued_at = now();
            $return->loadMissing('returnProducts');
            $refundAmount = (float) $return->returnProducts->sum('return_price');

            // Attempt Stripe refund if enabled
            $stripeEnabled = Settings::group('integrations')->get('stripe_refund_enabled');
            $stripeSecret  = Settings::group('integrations')->get('stripe_refund_secret_key');

            if ((int) $stripeEnabled === 5 && $stripeSecret) {
                try {
                    $transaction = \App\Models\Transaction::where('order_id', $return->order_id)
                        ->where('sign', '+')
                        ->first();
                    if ($transaction && $transaction->transaction_no) {
                        $stripe = new \Stripe\StripeClient($stripeSecret);
                        $stripeRefund = $stripe->refunds->create([
                            'charge' => $transaction->transaction_no,
                            'amount' => (int) ($refundAmount * 100),
                        ]);
                        $return->reject_reason = 'STRIPE_REFUND_ID:' . $stripeRefund->id;
                    }
                } catch (\Exception $stripeEx) {
                    \Illuminate\Support\Facades\Log::error('[STRIPE_REFUND] ' . $stripeEx->getMessage());
                    session()->flash('warning', 'Refund status updated but Stripe API refund failed: ' . $stripeEx->getMessage());
                }
            }
        }

        $return->save();

        // Audit
        $order = Order::find($return->order_id);
        if ($order) {
            AuditLogger::refundStageChanged($order, $newRefundStatus, (float) $return->returnProducts->sum('return_price'));
        }

        $labels = [
            RefundStatus::ITEM_RECEIVED => 'Item marked as received — ready to issue refund.',
            RefundStatus::REFUND_ISSUED => 'Refund issued successfully — customer balance has been credited.',
        ];
        return back()->with('success', $labels[$newRefundStatus] ?? 'Refund status updated.');
    }
}
