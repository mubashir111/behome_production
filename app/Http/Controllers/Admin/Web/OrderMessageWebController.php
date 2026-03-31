<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;

class OrderMessageWebController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all | cancellation | unread

        $query = Order::with(['user', 'messages' => fn($q) => $q->orderBy('created_at')])
            ->whereHas('messages')
            ->latest();

        if ($filter === 'cancellation') {
            $query->whereHas('messages', fn($q) =>
                $q->where('message', 'like', '[CANCELLATION REQUEST]%')
            );
        } elseif ($filter === 'unread') {
            $query->whereHas('messages', fn($q) =>
                $q->where('sender_type', 'customer')->where('is_read', false)
            );
        }

        $orders = $query->paginate(20)->appends(['filter' => $filter]);

        // Mark all customer messages on the current page as read now that admin has seen them
        $pageOrderIds = $orders->getCollection()->pluck('id');
        OrderMessage::whereIn('order_id', $pageOrderIds)
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $unreadCount       = OrderMessage::where('sender_type', 'customer')->where('is_read', false)->count();
        $cancellationCount = OrderMessage::where('message', 'like', '[CANCELLATION REQUEST]%')->count();

        // Lightweight poll endpoint — JS checks this for new unread count
        if ($request->get('_poll')) {
            return response()->json(['unreadCount' => $unreadCount, 'cancellationCount' => $cancellationCount]);
        }

        return view('admin.order-messages.index', compact('orders', 'filter', 'unreadCount', 'cancellationCount'));
    }

    public function reply(Request $request, Order $order)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $order->messages()->create([
            'user_id'     => auth()->id(),
            'sender_type' => 'admin',
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        // Mark all customer messages on this order as read
        $order->messages()->where('sender_type', 'customer')->update(['is_read' => true]);

        return back()->with('success', 'Reply sent.');
    }
}
