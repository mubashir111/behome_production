@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Payments</h1>
            <p class="admin-page-subtitle">All payment transactions — online payments and cash collections.</p>
        </div>
    </div>

    @include('admin._alerts')

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="admin-card p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Collected</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $currencySymbol }}{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Refunded</p>
            <p class="text-2xl font-bold text-rose-500">{{ $currencySymbol }}{{ number_format($totalRefund, 2) }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Net Revenue</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $currencySymbol }}{{ number_format($totalPaid - $totalRefund, 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-card mb-6">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap gap-3 items-end p-1">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Order #, transaction ID, customer…"
                    class="admin-form-input w-full" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Payment Method</label>
                <select name="gateway" class="admin-form-select">
                    <option value="">All methods</option>
                    @foreach($gateways as $gw)
                        @php
                            $gwLabels = ['cashondelivery'=>'Cash on Delivery','credit'=>'Store Credit','stripe'=>'Stripe','paypal'=>'PayPal','razorpay'=>'Razorpay','bkash'=>'bKash','mollie'=>'Mollie','flutterwave'=>'Flutterwave'];
                            $gwDisplay = $gwLabels[$gw] ?? ucfirst($gw);
                        @endphp
                        <option value="{{ $gw }}" {{ request('gateway') === $gw ? 'selected' : '' }}>{{ $gwDisplay }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Type</label>
                <select name="type" class="admin-form-select">
                    <option value="">All types</option>
                    <option value="payment"   {{ request('type') === 'payment'   ? 'selected' : '' }}>Payment</option>
                    <option value="cash_back" {{ request('type') === 'cash_back' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="admin-form-input" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="admin-form-input" />
            </div>
            <button type="submit" class="admin-btn-primary px-5">Filter</button>
            @if(request()->hasAny(['search','gateway','type','status','date_from','date_to']))
                <a href="{{ route('admin.payments.index') }}" class="admin-btn-secondary px-5">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Transaction ID</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Order</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Customer</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Method</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Type</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600">Amount</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Date</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $txn)
                    @php
                        $order = $txn->order;
                        $user  = $order?->user;
                        $isRefund = $txn->type === 'cash_back' || $txn->sign === '-';
                        $gwLabels = ['cashondelivery'=>'Cash on Delivery','credit'=>'Store Credit','stripe'=>'Stripe','paypal'=>'PayPal','razorpay'=>'Razorpay','bkash'=>'bKash','mollie'=>'Mollie','flutterwave'=>'Flutterwave'];
                        $gwDisplay = $gwLabels[$txn->payment_method] ?? ucfirst($txn->payment_method ?? '—');
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-slate-400 max-w-[160px]">
                            <span class="truncate block" title="{{ $txn->transaction_no }}">{{ $txn->transaction_no ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($order)
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-indigo-600 hover:underline">
                                    #{{ $order->order_serial_no ?? $order->id }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user)
                                <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            @else
                                <span class="text-slate-400">Guest</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold">
                                {{ $gwDisplay }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isRefund)
                                <span class="px-2 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold">Refund</span>
                            @else
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Payment</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $isRefund ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ $isRefund ? '-' : '' }}{{ $currencySymbol }}{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $txn->created_at->format('M d, Y') }}<br>
                            <span class="text-slate-400">{{ $txn->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order)
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                                    View Order
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            No payment transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="px-4 py-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
