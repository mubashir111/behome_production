@extends('layouts.admin')
@section('title', 'Ticker / Benefits')
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
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Ticker / Benefits</h2>
        <p class="text-slate-500 mt-1">Manage the scrolling marquee ticker items shown on the homepage.</p>
    </div>
    <a href="{{ route('admin.benefits.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Ticker Item
    </a>
</div>
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
<div class="glass p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="text-slate-400 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </span>
    <input id="benefits-search" type="text" placeholder="Search benefits…" class="flex-1 bg-transparent border-none outline-none focus:outline-none text-slate-900" />
</div>
    </div>
    <table id="benefits-table" class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Icon</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Title</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Description</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Sort</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Status</th>
                <th class="text-right px-6 py-4 font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($benefits as $benefit)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                    <img src="{{ $benefit->thumb }}" alt="{{ $benefit->title }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200">
                </td>
                <td class="px-6 py-4 font-semibold text-slate-900">{{ $benefit->title }}</td>
                <td class="px-6 py-4 text-slate-500 max-w-xs truncate">{{ $benefit->description ?: '—' }}</td>
                <td class="px-6 py-4 text-slate-500">{{ $benefit->sort }}</td>
                <td class="px-6 py-4">
                    @if($benefit->status == 5)
                        <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-lg">Active</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-100 rounded-lg">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.benefits.edit', $benefit) }}" class="px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-all">Edit</a>
                        <button type="button"
                            onclick="confirmSubmit('del-benefit-{{ $benefit->id }}', { title: 'Delete Item', message: 'Are you sure you want to delete this ticker item? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })"
                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all">
                            Delete
                        </button>
                        <form id="del-benefit-{{ $benefit->id }}" action="{{ route('admin.benefits.destroy', $benefit) }}" method="POST" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                    <p class="font-medium text-slate-600">No ticker items yet</p>
                    <p class="text-sm mt-1">Add items to populate the homepage marquee ticker.</p>
                    <a href="{{ route('admin.benefits.create') }}" class="mt-3 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 transition-all">Add First Item</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($benefits->hasPages())
<div class="mt-6">{{ $benefits->links() }}</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('benefits-search', 'benefits-table');</script>
@endpush
