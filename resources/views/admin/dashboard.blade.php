@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

{{-- ══ Hero Banner ══ --}}
<div class="dash-hero animate-in">
    <div class="relative z-10">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-semibold text-white/55 uppercase tracking-widest mb-2">
                    Store Overview &mdash; {{ now()->format('l, d F Y') }}
                </p>
                <h1 class="text-3xl md:text-4xl font-bold text-white">
                    Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name ?? 'Admin')[0] }} 👋
                </h1>
                <p class="text-sm text-white/60 mt-2">
                    Here's what's happening with your store today.
                </p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/12 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-semibold transition-all">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    View Orders
                </a>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-indigo-600 rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Product
                </a>
            </div>
        </div>

        {{-- Mini metric pills inside hero --}}
        <div class="flex flex-wrap gap-3">
            <div class="glass px-5 py-3 rounded-xl flex items-center gap-3">
                <span class="text-lg font-bold text-slate-900 font-outfit">{{ $currencySymbol }}{{ number_format($stats['total_revenue'], 0) }}</span>
                <span class="text-xs text-slate-900 font-semibold">Total Revenue</span>
                <span class="text-xs text-slate-900">↑ Active</span>
            </div>
            <div class="glass px-5 py-3 rounded-xl flex items-center gap-3">
                <span class="text-lg font-bold text-slate-900 font-outfit">{{ $stats['active_orders'] }}</span>
                <span class="text-xs text-slate-900 font-semibold">Active Orders</span>
            </div>
            <div class="glass px-5 py-3 rounded-xl flex items-center gap-3">
                <span class="text-lg font-bold text-slate-900 font-outfit">{{ $stats['total_customers'] }}</span>
                <span class="text-xs text-slate-900 font-semibold">Customers</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ KPI Stat Cards ══ --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-emerald-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <a href="{{ route('admin.orders.index') }}?status=10" class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full hover:bg-emerald-100 transition">↑ Revenue</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($stats['total_revenue'], 2) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Total Revenue</div>
    </div>

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-indigo-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-indigo-800 bg-indigo-50 px-2.5 py-1 rounded-full hover:bg-indigo-100 transition">View all</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ number_format($stats['total_orders'] ?? 0) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Total Orders</div>
        @php $unviewedDash = \App\Models\Order::whereNull('admin_viewed_at')->count(); @endphp
        @if($unviewedDash > 0)
        <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 rounded-full">
            <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-pulse"></span>
            <span class="text-xs font-bold text-indigo-900">{{ $unviewedDash }} new</span>
        </div>
        @endif
    </div>

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-amber-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-full hover:bg-amber-100 transition">View all</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ number_format($stats['total_customers']) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Total Customers</div>
    </div>

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-pink-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-pink-800 bg-pink-50 px-2.5 py-1 rounded-full hover:bg-pink-100 transition">Inventory</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ number_format($stats['total_products']) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Total Products</div>
    </div>

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-yellow-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="text-xs font-bold text-yellow-800 bg-yellow-50 px-2.5 py-1 rounded-full hover:bg-yellow-100 transition">View all</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ number_format($stats['total_reviews']) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Reviews</div>
    </div>

    <div class="stat-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-icon bg-red-100 rounded-xl p-3">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
            <a href="{{ route('admin.returns.index') }}" class="text-xs font-bold text-red-800 bg-red-50 px-2.5 py-1 rounded-full hover:bg-red-100 transition">View all</a>
        </div>
        <div class="stat-value text-2xl font-bold text-slate-900">{{ number_format($pending_return_list->count()) }}</div>
        <div class="stat-label text-sm font-semibold text-slate-800">Pending Returns</div>
    </div>

</div>

{{-- ══ Quick Actions ══ --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('admin.products.create') }}" class="admin-card group hover:shadow-lg hover:scale-105 transition">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-indigo-100 to-violet-100 rounded-xl p-3 group-hover:scale-110 transition">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Add Product</div>
                <p class="text-xs text-slate-500">Create a new listing</p>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.orders.index') }}" class="admin-card group hover:shadow-lg hover:scale-105 transition">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl p-3 group-hover:scale-110 transition">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Manage Orders</div>
                <p class="text-xs text-slate-500">{{ $stats['active_orders'] }} active now</p>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.coupons.index') }}" class="admin-card group hover:shadow-lg hover:scale-105 transition">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-pink-100 to-rose-100 rounded-xl p-3 group-hover:scale-110 transition">
                <svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Coupons</div>
                <p class="text-xs text-slate-500">Manage discount codes</p>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.customers.index') }}" class="admin-card group hover:shadow-lg hover:scale-105 transition">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-amber-100 to-yellow-100 rounded-xl p-3 group-hover:scale-110 transition">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-slate-900 text-sm">Customers</div>
                <p class="text-xs text-slate-500">{{ $stats['total_customers'] }} registered</p>
            </div>
        </div>
    </a>
</div>

{{-- ══ Alert Banners: Cancellation Requests, Unread Messages & Pending Returns ══ --}}
@php $pending_returns_count = $stats['pending_returns'] ?? 0; @endphp
@if($cancellation_requests > 0 || $unread_messages > 0 || $pending_returns_count > 0)
<div class="grid grid-cols-1 gap-3 mb-6">

    @if($cancellation_requests > 0)
    <a href="{{ route('admin.order-messages.index', ['filter' => 'cancellation']) }}"
       class="admin-card group border-2 border-red-200 hover:border-red-300 hover:shadow-lg hover:shadow-red-100 transition">
        <div class="flex items-center gap-4">
            <div class="bg-red-100 rounded-xl p-3 flex-shrink-0 group-hover:bg-red-200 transition">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-red-700 text-sm">{{ $cancellation_requests }} Cancellation Request{{ $cancellation_requests > 1 ? 's' : '' }}</p>
                <p class="text-xs text-red-600 mt-0.5">Customers requesting order cancellation — review now</p>
            </div>
            <span class="text-xs font-bold text-red-700 whitespace-nowrap flex-shrink-0">Review →</span>
        </div>
    </a>
    @endif

    @if($unread_messages > 0)
    <a href="{{ route('admin.order-messages.index', ['filter' => 'unread']) }}"
       class="admin-card group border-2 border-amber-200 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-100 transition">
        <div class="flex items-center gap-4">
            <div class="bg-amber-100 rounded-xl p-3 flex-shrink-0 group-hover:bg-amber-200 transition">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-amber-800 text-sm">{{ $unread_messages }} Unread Message{{ $unread_messages > 1 ? 's' : '' }}</p>
                <p class="text-xs text-amber-700 mt-0.5">Customers are waiting for replies on their orders</p>
            </div>
            <span class="text-xs font-bold text-amber-700 whitespace-nowrap flex-shrink-0">Reply →</span>
        </div>
    </a>
    @endif

    @if($pending_returns_count > 0)
    <a href="{{ route('admin.returns.index') }}"
       class="admin-card group border-2 border-orange-200 hover:border-orange-300 hover:shadow-lg hover:shadow-orange-100 transition">
        <div class="flex items-center gap-4">
            <div class="bg-orange-100 rounded-xl p-3 flex-shrink-0 group-hover:bg-orange-200 transition">
                <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-orange-800 text-sm">{{ $pending_returns_count }} Pending Return{{ $pending_returns_count > 1 ? 's' : '' }}</p>
                <p class="text-xs text-orange-700 mt-0.5">Return &amp; refund requests awaiting your review</p>
            </div>
            <span class="text-xs font-bold text-orange-700 whitespace-nowrap flex-shrink-0">Review →</span>
        </div>
    </a>
    @endif

</div>
@endif

{{-- ══ Main Grid: Orders + Right Column ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    {{-- Recent Orders --}}
    <div class="admin-card lg:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-4 border-b border-slate-200">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Recent Orders</h3>
                <p class="text-xs text-slate-500 mt-1">Latest {{ count($recent_orders) }} transactions</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all →</a>
        </div>
        <div class="overflow-x-auto -mx-6 -mb-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-t border-b border-slate-200">
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_orders as $order)
                    @php
                        $statusMap = [
                            1  => ['label' => 'Pending',   'class' => 'badge-pending'],
                            5  => ['label' => 'Confirmed', 'class' => 'badge-confirmed'],
                            7  => ['label' => 'Ongoing',   'class' => 'badge-ongoing'],
                            10 => ['label' => 'Delivered', 'class' => 'badge-delivered'],
                        ];
                        $s = $statusMap[$order->status] ?? ['label' => 'Unknown', 'class' => 'badge-unknown'];
                        $initials = strtoupper(substr($order->user->name ?? 'G', 0, 1));
                        $colors = ['#6366f1','#10b981','#f59e0b','#ec4899','#3b82f6','#8b5cf6'];
                        $color = $colors[ord($initials) % count($colors)];
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                        <td class="px-6 py-3 whitespace-nowrap font-bold text-indigo-600">#{{ $order->order_serial_no }}</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:{{ $color }};">
                                    {{ $initials }}
                                </div>
                                <span class="font-semibold text-slate-900">{{ $order->user->name ?? 'Guest' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-slate-600 whitespace-nowrap text-sm">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3">
                            <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                        </td>
                        <td class="px-6 py-3 text-right font-bold text-slate-900 whitespace-nowrap">{{ $currencySymbol }}{{ number_format($order->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="flex flex-col gap-6 lg:col-span-1">

        {{-- Recent Reviews --}}
        <div class="admin-card">
            <div class="flex items-center justify-between gap-3 mb-4 pb-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-base">Recent Reviews</h3>
                <a href="{{ route('admin.reviews.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all →</a>
            </div>
            <div class="max-h-72 overflow-y-auto">
                @forelse($recent_reviews as $review)
                <div class="py-3 px-0 border-t border-slate-100 first:border-0 first:pt-0 flex items-start gap-3">
                    {{-- Star badge --}}
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0 {{ $review->star >= 4 ? 'bg-emerald-50 text-emerald-600' : ($review->star >= 3 ? 'bg-amber-50 text-amber-500' : 'bg-red-50 text-red-600') }}">
                        {{ $review->star }}★
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $review->product->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">by {{ $review->user->name ?? 'Guest' }} · {{ $review->created_at->diffForHumans() }}</p>
                        @if($review->review)
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $review->review }}</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.reviews.show', $review) }}" class="text-xs font-bold text-indigo-600 whitespace-nowrap flex-shrink-0 px-2.5 py-1 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">View</a>
                </div>
                @empty
                <p class="py-8 text-center text-slate-500 text-sm">No reviews yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Pending Returns --}}
        <div class="admin-card">
            <div class="flex items-center justify-between gap-3 mb-4 pb-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-base">Pending Returns</h3>
                <a href="{{ route('admin.returns.index') }}" class="text-sm font-bold text-orange-600 hover:text-orange-700">View all →</a>
            </div>
            <div class="max-h-60 overflow-y-auto">
                @forelse($pending_return_list as $ret)
                <div class="py-3 px-0 border-t border-slate-100 first:border-0 first:pt-0 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900">Order #{{ $ret->order_serial_no }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $ret->user->name ?? '—' }} · {{ $ret->returnReason->reason ?? 'No reason' }}</p>
                    </div>
                    <a href="{{ route('admin.returns.show', $ret) }}" class="text-xs font-bold text-orange-600 whitespace-nowrap flex-shrink-0 px-2.5 py-1 bg-orange-50 rounded-lg hover:bg-orange-100 transition">Review</a>
                </div>
                @empty
                <p class="py-8 text-center text-slate-500 text-sm">No pending returns. 🎉</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Order Messages --}}
        @if($recent_messages->count() > 0)
        <div class="admin-card">
            <div class="flex items-center justify-between gap-3 mb-4 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-900 text-base">Order Messages</h3>
                    @if($unread_messages > 0)
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">{{ $unread_messages }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.order-messages.index') }}" class="text-sm font-bold text-amber-600 hover:text-amber-700">View all →</a>
            </div>
            <div class="max-h-64 overflow-y-auto">
                @foreach($recent_messages as $order)
                @php
                    $lastMsg = $order->messages->first();
                    $hasCancelReq = $order->messages->contains(fn($m) => str_starts_with($m->message, '[CANCELLATION REQUEST]'));
                @endphp
                <div class="py-3 px-0 border-t border-slate-100 first:border-0 first:pt-0 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $hasCancelReq ? 'bg-red-50' : 'bg-amber-50' }}">
                        @if($hasCancelReq)
                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900">
                            {{ $hasCancelReq ? '⚠ ' : '' }}Order #{{ $order->order_serial_no }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                            {{ $order->user->name ?? 'Customer' }}
                            @if($lastMsg)
                             · {{ Str::limit(str_replace('[CANCELLATION REQUEST]', '', $lastMsg->message), 40) }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold whitespace-nowrap flex-shrink-0 px-2.5 py-1 rounded-lg transition {{ $hasCancelReq ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-amber-600 bg-amber-50 hover:bg-amber-100' }}">Reply</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /right column --}}

</div>

@endsection
