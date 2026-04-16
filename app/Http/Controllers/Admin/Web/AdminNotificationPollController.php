<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\ReturnAndRefund;
use App\Models\AdminNotification;
use App\Enums\ReturnOrderStatus;
use App\Libraries\AppLibrary;

class AdminNotificationPollController extends Controller
{
    public function poll(Request $request)
    {
        $since = $request->get('since')
            ? Carbon::createFromTimestampMs((int) $request->get('since'))
            : now()->subSeconds(35);

        $ordersUrl       = route('admin.orders.index');
        $msgsUrl         = route('admin.order-messages.index');
        $returnsUrl      = route('admin.returns.index');

        $newOrders = Order::where('created_at', '>', $since)->latest()->take(5)->get(['id','order_serial_no','total','created_at']);
        $newOrderCount = $newOrders->count();

        $unviewedTotal  = Order::whereNull('admin_viewed_at')->count();
        $unreadMessages = OrderMessage::where('sender_type', 'customer')->where('is_read', false)->count();
        $cancellations  = OrderMessage::where('message', 'like', '[CANCELLATION REQUEST]%')
            ->where('created_at', '>', $since)->count();

        $newReturns      = ReturnAndRefund::where('created_at', '>', $since)->count();
        $pendingReturns  = ReturnAndRefund::where('status', ReturnOrderStatus::PENDING)->count();
        $unviewedReturns = ReturnAndRefund::whereNull('admin_viewed_at')->count();

        // ── Persist new events as AdminNotification rows ──
        foreach ($newOrders as $o) {
            AdminNotification::record(
                'order',
                'New Order #' . $o->order_serial_no,
                'Total: ' . AppLibrary::currencyAmountFormat($o->total),
                $ordersUrl,
                'cart'
            );
        }
        if ($cancellations > 0) {
            AdminNotification::record(
                'cancellation',
                $cancellations . ' Cancellation Request(s)',
                'Customer(s) requested order cancellation.',
                $msgsUrl . '?filter=cancellation',
                'warning'
            );
        }
        if ($newReturns > 0) {
            AdminNotification::record(
                'return',
                $newReturns . ' New Return Request(s)',
                'Customer(s) submitted a return/refund request.',
                $returnsUrl,
                'return'
            );
        }

        // ── Bell dropdown: last 20 notifications ──
        $recentNotifications = AdminNotification::orderBy('created_at', 'desc')
            ->limit(20)->get(['id','type','title','body','link','icon','is_read','created_at']);

        $unreadCount = AdminNotification::where('is_read', false)->count();

        return response()->json([
            'new_orders'          => $newOrderCount,
            'orders'              => $newOrders,
            'unviewed_total'      => $unviewedTotal,
            'unread_msgs'         => $unreadMessages,
            'cancellations'       => $cancellations,
            'new_returns'         => $newReturns,
            'pending_returns'     => $pendingReturns,
            'unviewed_returns'    => $unviewedReturns,
            'notifications'       => $recentNotifications,
            'unread_notif_count'  => $unreadCount,
            'server_time'         => now()->valueOf(),
        ]);
    }

    public function markRead()
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function clear()
    {
        AdminNotification::truncate();
        return response()->json(['success' => true]);
    }
}
