@extends('layouts.admin')
@section('title', 'Promotions')
@section('content')

@if(session('success'))
<div class="glass border-l-4 border-emerald-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
    <div class="flex items-center">
        <svg class="h-5 w-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
    </div>
    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
</div>
@endif

<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Promotions</h2>
        <p class="text-slate-500 mt-1">Manage homepage promotion banners.</p>
    </div>
    <a href="{{ route('admin.promotions.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Promotion
    </a>
</div>

<div class="glass p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="text-slate-400 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </span>
    <input id="promotions-search" type="text" placeholder="Search promotions…" class="flex-1 bg-transparent border-none outline-none focus:outline-none text-slate-900" />
</div>

{{-- Type legend --}}
<div class="flex gap-3 mb-6 flex-wrap">
    <span class="px-3 py-1.5 text-xs font-semibold bg-purple-100 text-purple-700 rounded-xl" title="Type 10">Homepage Left Banner (Tall)</span>
    <span class="px-3 py-1.5 text-xs font-semibold bg-sky-100 text-sky-700 rounded-xl" title="Type 5">Homepage Right Banners (Stacked)</span>
    <span class="px-3 py-1.5 text-xs font-semibold bg-amber-100 text-amber-700 rounded-xl" title="Type 1">Hero Slider Card (Editorial)</span>
    <span class="px-3 py-1.5 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-xl" title="Type 15">Feature Section Banner</span>
</div>
<div id="promotions-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($promotions as $promotion)
    <div data-search-item class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="relative h-44 bg-slate-100">
            <img src="{{ $promotion->cover }}" alt="{{ $promotion->name }}" class="w-full h-full object-cover">
            <div class="absolute top-3 right-3 flex gap-1.5">
                @if($promotion->status == 5)
                    <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/90 backdrop-blur rounded-lg">Active</span>
                @else
                    <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-100/90 backdrop-blur rounded-lg">Inactive</span>
                @endif
                @php
                    $typeColors = [1 => 'bg-amber-300/90 text-amber-900', 5 => 'bg-sky-300/90 text-sky-900', 10 => 'bg-purple-300/90 text-purple-900', 15 => 'bg-emerald-300/90 text-emerald-900'];
                    $typeLabels = [1 => 'Hero Slider Card', 5 => 'Right Banner', 10 => 'Left Banner', 15 => 'Feature Banner'];
                @endphp
                <span class="px-2.5 py-1 text-xs font-bold backdrop-blur rounded-lg {{ $typeColors[$promotion->type] ?? 'bg-slate-300/90 text-slate-900' }}">
                    {{ $typeLabels[$promotion->type] ?? 'Type '.$promotion->type }}
                </span>
            </div>
        </div>
        <div class="p-5">
            <h3 class="text-base font-bold text-slate-900 truncate">{{ $promotion->name }}</h3>
            @if($promotion->subtitle)
            <p class="text-sm text-slate-500 mt-1 truncate">{{ $promotion->subtitle }}</p>
            @endif
            @if($promotion->link)
            <span class="mt-2 inline-block text-xs font-mono text-slate-400 bg-slate-100 px-2 py-1 rounded-lg truncate max-w-full">{{ $promotion->link }}</span>
            @endif
            <div class="mt-4 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.promotions.edit', $promotion) }}" class="px-4 py-2 text-sm font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-all">Edit</a>
                <button type="button"
                    onclick="confirmSubmit('del-promo-{{ $promotion->id }}', { title: 'Delete Promotion', message: 'Are you sure you want to delete this promotion? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })"
                    class="px-4 py-2 text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all">
                    Delete
                </button>
                <form id="del-promo-{{ $promotion->id }}" action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" style="display:none;">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-24 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="text-lg font-medium text-slate-900">No promotions yet</p>
        <a href="{{ route('admin.promotions.create') }}" class="mt-4 inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-500 transition-all">Add First Promotion</a>
    </div>
    @endforelse
</div>
@if($promotions->hasPages())
<div class="mt-6">{{ $promotions->links() }}</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('promotions-search', 'promotions-grid');</script>
@endpush
