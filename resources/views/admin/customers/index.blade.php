@extends('layouts.admin')

@section('title', 'Customers')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold font-outfit text-slate-900">Customers</h2>
        <p class="text-slate-500 mt-1 text-sm">Manage your customer base and their accounts.</p>
    </div>
    <div class="text-sm text-slate-500 font-medium">
        {{ $customers->total() }} total customers
    </div>
</div>

{{-- Search / filter bar --}}
<form method="GET" action="{{ route('admin.customers.index') }}" class="mb-6 flex gap-3">
    <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="Search by name or email…"
        class="flex-1 max-w-sm px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
    />
    <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all">Search</button>
    @if(request('search'))
    <a href="{{ route('admin.customers.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Clear</a>
    @endif
</form>

<div class="admin-table-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="admin-table-head">
                <tr>
                    <th class="admin-table-head-cell">Customer</th>
                    <th class="admin-table-head-cell hidden md:table-cell">Phone</th>
                    <th class="admin-table-head-cell hidden lg:table-cell">Joined</th>
                    <th class="admin-table-head-cell">Orders</th>
                    <th class="admin-table-head-cell hidden sm:table-cell">Status</th>
                    <th class="admin-table-head-cell text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="admin-table-body">
                @forelse($customers as $customer)
                <tr class="admin-table-row">
                    <td class="admin-table-cell">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $customer->name }}</p>
                                <p class="text-xs text-slate-500">{{ $customer->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="admin-table-cell hidden md:table-cell text-sm text-slate-600">
                        {{ $customer->phone ?: '—' }}
                    </td>
                    <td class="admin-table-cell hidden lg:table-cell text-sm text-slate-600">
                        {{ $customer->created_at->format('d M Y') }}
                    </td>
                    <td class="admin-table-cell">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-50 text-slate-700 text-sm font-bold">
                            {{ $customer->orders_count ?? 0 }}
                        </span>
                    </td>
                    <td class="admin-table-cell hidden sm:table-cell">
                        @if($customer->status == 1)
                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg text-emerald-700 bg-emerald-50">Active</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg text-rose-700 bg-rose-50">Inactive</span>
                        @endif
                    </td>
                    <td class="admin-table-actions">
                        <a href="{{ route('admin.customers.show', $customer) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center text-slate-400">
                            <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-500">No customers found.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customers->hasPages())
<div class="mt-6">
    {{ $customers->links() }}
</div>
@endif

@endsection
