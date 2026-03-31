@extends('layouts.admin')

@section('title', 'Purchases')

@section('content')
<div class="max-w-[1200px] mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Purchase Orders</h2>
            <p class="text-slate-500 mt-1">Manage supplier purchases and inventory intake.</p>
        </div>
        <a href="{{ route('admin.purchases.create') }}" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
            + New Purchase
        </a>
    </div>

    @include('admin._alerts')

    <!-- Purchases Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="purchases-search" type="text" placeholder="Search PO or supplier…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="purchases-table" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Date / PO Ref</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Supplier</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Amount</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</span>
                                    <span class="text-xs text-slate-500 font-medium font-mono mt-1">PO-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-sm font-medium text-slate-600">
                                {{ $purchase->supplier->name ?? 'Unknown Supplier' }}
                            </td>
                            <td class="px-6 py-6 font-bold text-indigo-600">
                                {{ env('CURRENCY_SYMBOL', '₹') }}{{ number_format($purchase->total, 2) }}
                            </td>
                            <td class="px-6 py-6">
                                @if($purchase->status == 15)
                                    <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold tracking-wide">RECEIVED</span>
                                @elseif($purchase->status == 10)
                                    <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs font-bold tracking-wide">ORDERED</span>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold tracking-wide">PENDING</span>
                                @endif
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" title="View Details"
                                       class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.purchases.edit', $purchase->id) }}" title="Edit Purchase"
                                       class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmSubmit('del-purchase-{{ $purchase->id }}', { title: 'Delete Purchase Request', message: 'Deleting this will remove stock history associated with it. Do you want to continue?', confirmText: 'Yes, Delete', type: 'danger' })"
                                            class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    <form id="del-purchase-{{ $purchase->id }}" action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                                No purchase orders found. Click "+ New Purchase" to start.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($purchases) && method_exists($purchases, 'hasPages') && $purchases->hasPages())
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-200">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    if(typeof adminSearch === 'function') adminSearch('purchases-search', 'purchases-table');
</script>
@endpush
