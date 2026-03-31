@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Coupons</h2>
            <p class="text-slate-500 mt-1">Create and manage your promotional discount codes.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                + New Coupon
            </a>
    </div>

    @include('admin._alerts')

    <!-- Coupons Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="coupons-search" type="text" placeholder="Search coupons…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
        </div>
        <div class="overflow-x-auto">
            <table id="coupons-table" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Coupon Info</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Discount</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Validity</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center overflow-hidden">
                                        <img src="{{ $coupon->image }}" alt="{{ $coupon->code }}" class="w-8 h-8 object-contain">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900">{{ $coupon->name }}</h4>
                                        <p class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded mt-1 inline-block">{{ $coupon->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-medium text-slate-600">
                                <div class="flex flex-col">
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ $coupon->discount_type == 5 ? $coupon->discount . '%' : '$' . $coupon->discount }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Min Order: ${{ $coupon->minimum_order }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 text-[8px]"></span>
                                        {{ $coupon->start_date->format('M d, Y') }}
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-2 h-2 rounded-full bg-rose-400 text-[8px]"></span>
                                        {{ $coupon->end_date->format('M d, Y') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" 
                                       class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmSubmit('del-coupon-{{ $coupon->id }}', { title: 'Delete Coupon', message: 'Are you sure you want to delete this coupon? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })"
                                                class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    <form id="del-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                                No coupons found. Click "+ New Coupon" to start.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-200">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>adminSearch('coupons-search', 'coupons-table');</script>
@endpush
