@extends('layouts.admin')
@section('title', 'Brands')
@section('content')

@if(session('success'))
<div class="glass border-l-4 border-emerald-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
</div>
@endif

<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Brands</h2>
        <p class="text-slate-500 mt-1">Manage brand logos shown in the homepage brands section and shop filter.</p>
    </div>
    <a href="{{ route('admin.brands.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Brand
    </a>
</div>
<div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="brands-search" type="text" placeholder="Search brands…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
<div id="brands-grid" class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4">
    @forelse($brands as $brand)
    <div data-search-item class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 text-center group">
        <div class="h-16 flex items-center justify-center mb-3">
            <img src="{{ $brand->thumb }}" alt="{{ $brand->name }}" class="max-h-12 max-w-full object-contain">
        </div>
        <p class="text-sm font-semibold text-slate-800 truncate">{{ $brand->name }}</p>
        @if($brand->status == 5)
            <span class="text-xs text-emerald-600 font-medium">Active</span>
        @else
            <span class="text-xs text-rose-500 font-medium">Inactive</span>
        @endif
        <div class="mt-3 flex gap-1.5 justify-center">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-all">Edit</a>
            <button type="button"
                onclick="confirmSubmit('del-brand-{{ $brand->id }}', { title: 'Delete Brand', message: 'Are you sure you want to delete &quot;{{ addslashes($brand->name) }}&quot;? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })"
                class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all">
                Del
            </button>
            <form id="del-brand-{{ $brand->id }}" action="{{ route('admin.brands.destroy', $brand) }}" method="POST" style="display:none;">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-6 py-16 text-center">
        <p class="text-lg font-medium text-slate-900">No brands yet</p>
        <a href="{{ route('admin.brands.create') }}" class="mt-4 inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-500 transition-all">Add First Brand</a>
    </div>
    @endforelse
</div>
@if($brands->hasPages())
<div class="mt-6">{{ $brands->links() }}</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('brands-search', 'brands-grid');</script>
@endpush
