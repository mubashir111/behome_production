@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Orders</h2>
            <p class="admin-page-subtitle">Track and manage customer orders.</p>
        </div>
        @php $newOrderCount = $orders->getCollection()->whereNull('admin_viewed_at')->count(); @endphp
        @if($newOrderCount > 0)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 18px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);border:1px solid #c7d2fe;border-radius:14px;">
            <span style="width:8px;height:8px;background:#4f46e5;border-radius:50%;animation:pulse-badge 2s infinite;flex-shrink:0;"></span>
            <span style="font-size:13px;font-weight:700;color:#4338ca;">{{ $newOrderCount }} new unviewed order{{ $newOrderCount > 1 ? 's' : '' }}</span>
        </div>
        @endif
    </div>

    {{-- Toggle bar: Real Orders / Incomplete (payment abandoned) --}}
    <div class="flex gap-2 mb-5">
        <a href="{{ route('admin.orders.index') }}"
           class="px-4 py-2 text-sm font-semibold rounded-xl transition {{ !$showIncomplete ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Orders
        </a>
        <a href="{{ route('admin.orders.index', ['incomplete' => 1]) }}"
           class="px-4 py-2 text-sm font-semibold rounded-xl transition flex items-center gap-2 {{ $showIncomplete ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Incomplete Payments
            @if($incompleteCount > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full {{ $showIncomplete ? 'bg-white text-amber-600' : 'bg-amber-500 text-white' }}">{{ $incompleteCount }}</span>
            @endif
        </a>
    </div>

    @if($showIncomplete)
    <div style="display:flex;align-items:center;gap:10px;padding:12px 18px;background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;margin-bottom:20px;">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <p class="text-sm text-amber-800 font-medium m-0">These orders were created but payment was <strong>never completed</strong> — the customer cancelled or left the payment page. <strong>Do not process or fulfil these.</strong> They will be cleaned up automatically.</p>
    </div>
    @endif

    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-3 mb-6 flex-wrap">
        @if($showIncomplete)<input type="hidden" name="incomplete" value="1">@endif
        <input type="text" name="search" value="{{ $search ?? '' }}"
               placeholder="Search by order # or customer name/email..."
               class="flex-1 min-w-[220px] px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white" />
        @if(!$showIncomplete)
        <select name="status" class="px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">All Order Statuses</option>
            <option value="1"  {{ request('status')=='1'  ? 'selected':'' }}>Pending</option>
            <option value="5"  {{ request('status')=='5'  ? 'selected':'' }}>Confirmed</option>
            <option value="7"  {{ request('status')=='7'  ? 'selected':'' }}>On the Way</option>
            <option value="10" {{ request('status')=='10' ? 'selected':'' }}>Delivered</option>
            <option value="15" {{ request('status')=='15' ? 'selected':'' }}>Canceled</option>
            <option value="20" {{ request('status')=='20' ? 'selected':'' }}>Rejected</option>
        </select>
        <select name="payment_status" class="px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">All Payment Statuses</option>
            <option value="5"  {{ request('payment_status')=='5'  ? 'selected':'' }}>Paid</option>
            <option value="10" {{ request('payment_status')=='10' ? 'selected':'' }}>Unpaid / Pay on Delivery</option>
        </select>
        @endif
        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">Search</button>
        @if(request()->anyFilled(['search','status','payment_status']))
            <a href="{{ route('admin.orders.index', $showIncomplete ? ['incomplete'=>1] : []) }}" class="px-5 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">Clear</a>
        @endif
    </form>

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Order</th>
                        <th class="admin-table-head-cell">Customer</th>
                        <th class="admin-table-head-cell">Total</th>
                        <th class="admin-table-head-cell">Payment</th>
                        <th class="admin-table-head-cell">Order Status</th>
                        <th class="admin-table-head-cell">Date</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($orders as $order)
                    @php
                        $isNew   = !$order->admin_viewed_at;
                        $gateway = \App\Models\PaymentGateway::find($order->payment_method);
                        $gwSlug  = $gateway?->slug ?? '';
                        $isCod   = in_array($gwSlug, ['cashondelivery', 'credit']);
                        $isPaid  = $order->payment_status == 5;

                        // Human-readable payment label
                        if ($isPaid) {
                            $payLabel    = 'Paid';
                            $payClass    = 'text-emerald-700 bg-emerald-50';
                        } elseif ($isCod) {
                            $payLabel    = 'Pay on Delivery';
                            $payClass    = 'text-amber-700 bg-amber-50';
                        } else {
                            $payLabel    = 'Awaiting Payment';
                            $payClass    = 'text-rose-700 bg-rose-50';
                        }

                        // Gateway display name
                        $gwLabels = [
                            'cashondelivery' => 'Cash on Delivery',
                            'credit'         => 'Store Credit',
                            'stripe'         => 'Stripe',
                            'paypal'         => 'PayPal',
                            'razorpay'       => 'Razorpay',
                            'bkash'          => 'bKash',
                            'mollie'         => 'Mollie',
                            'flutterwave'    => 'Flutterwave',
                        ];
                        $gwName = $gwLabels[$gwSlug] ?? ucfirst($gwSlug ?: 'Unknown');

                        $statusClasses = [
                            1  => 'text-amber-700 bg-amber-50',
                            5  => 'text-blue-700 bg-blue-50',
                            7  => 'text-indigo-700 bg-indigo-50',
                            10 => 'text-emerald-700 bg-emerald-50',
                            15 => 'text-rose-700 bg-rose-50',
                            20 => 'text-gray-700 bg-gray-50',
                        ];
                        $statusText = [
                            1  => 'Pending',
                            5  => 'Confirmed',
                            7  => 'On the Way',
                            10 => 'Delivered',
                            15 => 'Canceled',
                            20 => 'Rejected',
                        ];
                        $payload = $order->reasonPayload();
                    @endphp
                    <tr class="admin-table-row" style="{{ $isNew ? 'background:linear-gradient(90deg,#eef2ff 0%,#fff 55%);border-left:3px solid #6366f1;' : '' }}">
                        <td class="admin-table-cell">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-slate-900">#{{ $order->order_serial_no }}</span>
                                @if(isset($payload['cancellation_requested']) && $payload['cancellation_requested'])
                                    <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;border-radius:100px;letter-spacing:0.05em;white-space:nowrap;">
                                        REQ. CANCEL
                                    </span>
                                @endif
                                @if($isNew)
                                    <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;background:#4f46e5;color:#fff;font-size:10px;font-weight:700;border-radius:100px;letter-spacing:0.05em;white-space:nowrap;">
                                        <span style="width:5px;height:5px;background:#a5b4fc;border-radius:50%;display:inline-block;"></span>NEW
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400">
                                {{ $order->order_type == 10 ? 'Pick Up' : ($order->order_type == 15 ? 'POS' : 'Delivery') }}
                                · {{ $order->source == 10 ? 'App' : ($order->source == 15 ? 'POS' : 'Web') }}
                            </span>
                        </td>
                        <td class="admin-table-cell">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ substr($order->user->name ?? 'C', 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-slate-900">{{ $order->user->name ?? 'Guest' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $order->user->email ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="admin-table-cell">
                            <span class="text-sm font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="admin-table-cell">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $payClass }}">
                                {{ $payLabel }}
                            </span>
                            <span class="block text-xs text-slate-400 mt-1">{{ $gwName }}</span>
                        </td>
                        <td class="admin-table-cell">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $statusClasses[$order->status] ?? 'text-slate-700 bg-slate-100' }}">
                                {{ $statusText[$order->status] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="admin-table-cell">
                            <span class="text-sm text-slate-600">{{ $order->created_at->format('M d, Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $order->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="admin-table-actions">
                            <div class="flex items-center gap-1 justify-end">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                   title="{{ $isNew ? 'View (unread)' : 'View Order' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                    onclick="confirmSubmit('del-order-{{ $order->id }}', { title: 'Archive Order', message: 'This order will be archived and hidden from the main list. You can restore it anytime from Archived Orders.', confirmText: 'Archive', type: 'danger' })"
                                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                    title="Archive Order">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12"/>
                                    </svg>
                                </button>
                                <form id="del-order-{{ $order->id }}" action="{{ route('admin.orders.destroy', $order) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="admin-table-cell py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-lg font-medium text-slate-900">No orders found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<style>
@keyframes pulse-badge {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>
@endsection
