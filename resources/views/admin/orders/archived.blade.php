@extends('layouts.admin')

@section('title', 'Archived Orders')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Archived Orders</h1>
            <p class="admin-page-subtitle">Deleted orders kept for reference. Restore or permanently remove them here.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
            ← Back to Orders
        </a>
    </div>

    @include('admin._alerts')

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Order #</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Customer</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600">Total</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Payment</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Placed On</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Archived On</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors opacity-75">
                        <td class="px-4 py-3 font-semibold text-indigo-600">
                            #{{ $order->order_serial_no ?? $order->id }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $order->user->name ?? 'Guest' }}</p>
                            <p class="text-xs text-slate-400">{{ $order->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">
                            {{ $currencySymbol }}{{ number_format($order->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order->payment_status == 5)
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Paid</span>
                            @else
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $order->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $order->deleted_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Restore -->
                                <form action="{{ route('admin.orders.restore', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                                        Restore
                                    </button>
                                </form>

                                <!-- Permanent Delete -->
                                <form id="force-del-{{ $order->id }}"
                                    action="{{ route('admin.orders.force-delete', $order->id) }}"
                                    method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button"
                                    onclick="confirmSubmit('force-del-{{ $order->id }}', { title: 'Permanently Delete', message: 'This will remove the order and all its data forever. This cannot be undone.', confirmText: 'Delete Forever', type: 'danger' })"
                                    class="px-3 py-1.5 text-xs font-semibold bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition">
                                    Delete Forever
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            No archived orders.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-4 py-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
