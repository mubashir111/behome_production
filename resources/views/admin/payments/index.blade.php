@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Payments</h1>
            <p class="admin-page-subtitle">All confirmed payment transactions</p>
        </div>
    </div>

    @include('admin._alerts')

    <!-- Stats row -->
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
                    placeholder="Order #, transaction, customer…"
                    class="admin-form-input w-full" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Gateway</label>
                <select name="gateway" class="admin-form-select">
                    <option value="">All gateways</option>
                    @foreach($gateways as $gw)
                        <option value="{{ $gw }}" {{ request('gateway') === $gw ? 'selected' : '' }}>{{ ucfirst($gw) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Payment Status</label>
                <select name="status" class="admin-form-select">
                    <option value="">All</option>
                    <option value="5"  {{ request('status') === '5'  ? 'selected' : '' }}>Paid</option>
                    <option value="10" {{ request('status') === '10' ? 'selected' : '' }}>Unpaid</option>
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
            @if(request()->hasAny(['search','gateway','status','date_from','date_to']))
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
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Transaction #</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Order #</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Customer</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Gateway</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600">Amount</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Payment Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Date</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $txn)
                    @php
                        $order = $txn->order;
                        $user  = $order?->user;
                        $isPaid = $order && $order->payment_status == 5;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">
                            {{ $txn->transaction_no ?? '—' }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-indigo-600">
                            @if($order)
                                <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">
                                    #{{ $order->order_serial_no ?? $order->id }}
                                </a>
                            @else
                                —
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
                            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-semibold capitalize">
                                {{ $txn->payment_method ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">
                            {{ $currencySymbol }}{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isPaid)
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Paid</span>
                            @else
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $txn->created_at->format('M d, Y H:i') }}
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
