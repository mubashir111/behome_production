@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<!-- Alerts -->
@if(session('success'))
    <div class="glass border-l-4 border-emerald-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0 text-emerald-500 mr-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 transition-colors">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
@endif
@if(session('error'))
    <div class="glass border-l-4 border-rose-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0 text-rose-500 mr-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 transition-colors">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
@endif

<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Categories</h2>
        <p class="text-slate-500 mt-1">Organize your products into logical categories.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Category
    </a>
</div>
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="categories-search" type="text" placeholder="Search categories…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
    </div>
    <div class="overflow-x-auto">
        <table id="categories-table" class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Category Name</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $category)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <span class="block text-sm font-bold text-slate-900">{{ $category->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono text-slate-500">{{ $category->slug }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($category->status == 5)
                            <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg">Active</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 rounded-lg">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button type="button" onclick="confirmSubmit('del-category-{{ $category->id }}', { title: 'Delete Category', message: 'Are you sure you want to delete this category? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            <form id="del-category-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <p class="text-lg font-medium text-slate-900">No categories found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>adminSearch('categories-search', 'categories-table');</script>
@endpush
