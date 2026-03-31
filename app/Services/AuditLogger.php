<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderAudit;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnOrderStatus;
use App\Enums\RefundStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    // ── Order status label map ─────────────────────────────────────────
    private static array $orderStatusLabels = [
        OrderStatus::PENDING    => 'Pending',
        OrderStatus::CONFIRMED  => 'Confirmed',
        OrderStatus::ON_THE_WAY => 'On the Way',
        OrderStatus::DELIVERED  => 'Delivered',
        OrderStatus::CANCELED   => 'Cancelled',
        OrderStatus::REJECTED   => 'Rejected',
    ];

    private static array $paymentStatusLabels = [
        PaymentStatus::PAID   => 'Paid',
        PaymentStatus::UNPAID => 'Unpaid',
    ];

    private static array $returnStatusLabels = [
        ReturnOrderStatus::PENDING  => 'Pending',
        ReturnOrderStatus::ACCEPT   => 'Accepted',
        ReturnOrderStatus::REJECTED => 'Rejected',
    ];

    private static array $refundStatusLabels = [
        RefundStatus::AWAITING_ITEM => 'Awaiting Item',
        RefundStatus::ITEM_RECEIVED => 'Item Received',
        RefundStatus::REFUND_ISSUED => 'Refund Issued',
    ];

    // ── Core write ─────────────────────────────────────────────────────

    public static function log(
        int    $orderId,
        string $event,
        string $description,
        array  $meta = [],
        string $actorType = 'system',
        ?int   $actorId = null,
        ?string $actorName = null
    ): void {
        try {
            OrderAudit::create([
                'order_id'    => $orderId,
                'event'       => $event,
                'description' => $description,
                'meta'        => empty($meta) ? null : $meta,
                'actor_type'  => $actorType,
                'actor_id'    => $actorId,
                'actor_name'  => $actorName,
            ]);
        } catch (\Throwable $e) {
            // Never let audit failures break the main flow
            Log::warning('AuditLogger failed: ' . $e->getMessage());
        }
    }

    // ── Resolve current actor ──────────────────────────────────────────

    private static function actor(): array
    {
        if (!Auth::check()) {
            return ['system', null, 'System'];
        }
        $user = Auth::user();
        // Determine actor type by guard or role
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super-admin') || request()->is('admin/*');
        return [$isAdmin ? 'admin' : 'customer', $user->id, $user->name];
    }

    // ── Named event helpers ────────────────────────────────────────────

    public static function orderPlaced(Order $order): void
    {
        [$type, $id, $name] = self::actor();
        self::log(
            $order->id,
            'order_placed',
            "Order #{$order->order_serial_no} placed successfully.",
            ['total' => $order->total, 'payment_method' => $order->payment_method],
            $type, $id, $name
        );
    }

    public static function paymentConfirmed(Order $order): void
    {
        self::log(
            $order->id,
            'payment_confirmed',
            "Payment confirmed. Order activated.",
            ['payment_status' => PaymentStatus::PAID],
            'system', null, 'Payment Gateway'
        );
    }

    public static function orderStatusChanged(Order $order, int $oldStatus, int $newStatus, ?string $reason = null): void
    {
        [$type, $id, $name] = self::actor();
        $from = self::$orderStatusLabels[$oldStatus] ?? "Status {$oldStatus}";
        $to   = self::$orderStatusLabels[$newStatus] ?? "Status {$newStatus}";
        $meta = ['from' => $oldStatus, 'to' => $newStatus, 'from_label' => $from, 'to_label' => $to];
        if ($reason) $meta['reason'] = $reason;

        self::log(
            $order->id,
            'status_changed',
            "Order status changed from {$from} to {$to}." . ($reason ? " Reason: {$reason}" : ''),
            $meta,
            $type, $id, $name
        );
    }

    public static function paymentStatusChanged(Order $order, int $oldStatus, int $newStatus): void
    {
        [$type, $id, $name] = self::actor();
        $from = self::$paymentStatusLabels[$oldStatus] ?? "Status {$oldStatus}";
        $to   = self::$paymentStatusLabels[$newStatus] ?? "Status {$newStatus}";

        self::log(
            $order->id,
            'payment_status_changed',
            "Payment status changed from {$from} to {$to}.",
            ['from' => $oldStatus, 'to' => $newStatus, 'from_label' => $from, 'to_label' => $to],
            $type, $id, $name
        );
    }

    public static function cancellationRequested(Order $order, string $reason = ''): void
    {
        [$type, $id, $name] = self::actor();
        self::log(
            $order->id,
            'cancellation_requested',
            "Customer requested cancellation." . ($reason ? " Reason: {$reason}" : ''),
            ['reason' => $reason],
            $type, $id, $name
        );
    }

    // ── Return / Refund events ─────────────────────────────────────────

    public static function returnSubmitted(Order $order, string $reasonTitle = '', string $note = ''): void
    {
        [$type, $id, $name] = self::actor();
        self::log(
            $order->id,
            'return_submitted',
            "Customer submitted a return/refund request." . ($reasonTitle ? " Reason: {$reasonTitle}" : ''),
            ['reason' => $reasonTitle, 'note' => $note],
            $type, $id, $name
        );
    }

    public static function returnStatusChanged(Order $order, int $oldStatus, int $newStatus, ?string $reason = null): void
    {
        [$type, $id, $name] = self::actor();
        $from = self::$returnStatusLabels[$oldStatus] ?? "Status {$oldStatus}";
        $to   = self::$returnStatusLabels[$newStatus] ?? "Status {$newStatus}";

        $eventMap = [
            ReturnOrderStatus::ACCEPT   => 'return_accepted',
            ReturnOrderStatus::REJECTED => 'return_rejected',
            ReturnOrderStatus::PENDING  => 'return_reset',
        ];
        $descMap = [
            ReturnOrderStatus::ACCEPT   => "Return request accepted. Customer instructed to ship item back.",
            ReturnOrderStatus::REJECTED => "Return request rejected." . ($reason ? " Reason: {$reason}" : ''),
            ReturnOrderStatus::PENDING  => "Return request reset to pending review.",
        ];

        $meta = ['from' => $oldStatus, 'to' => $newStatus, 'from_label' => $from, 'to_label' => $to];
        if ($reason) $meta['reason'] = $reason;

        self::log(
            $order->id,
            $eventMap[$newStatus] ?? 'return_status_changed',
            $descMap[$newStatus] ?? "Return status changed from {$from} to {$to}.",
            $meta,
            $type, $id, $name
        );
    }

    public static function refundStageChanged(Order $order, int $newRefundStatus, float $amount = 0): void
    {
        [$type, $id, $name] = self::actor();
        $label = self::$refundStatusLabels[$newRefundStatus] ?? "Stage {$newRefundStatus}";

        $descMap = [
            RefundStatus::ITEM_RECEIVED => "Returned item received by warehouse. Proceeding to inspection.",
            RefundStatus::REFUND_ISSUED => "Refund of {$amount} issued. Customer balance credited.",
        ];

        $meta = ['refund_status' => $newRefundStatus, 'label' => $label];
        if ($amount) $meta['amount'] = $amount;

        self::log(
            $order->id,
            'refund_stage_' . $newRefundStatus,
            $descMap[$newRefundStatus] ?? "Refund stage updated to {$label}.",
            $meta,
            $type, $id, $name
        );
    }
}
