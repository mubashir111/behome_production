@extends('layouts.admin')
@section('title', 'Static Pages')
@section('content')

<div class="max-w-[1200px] mx-auto pb-12">
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Static Pages</h2>
            <p class="text-slate-500 mt-1">Manage the content of your website's static pages.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1 text-sm">
            + New Page
        </a>
    </div>

    @include('admin._alerts')

    @if($pages->isEmpty())
        <div class="text-center py-20 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-medium italic">No pages yet. Click "+ New Page" to create one.</p>
        </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($pages as $page)
        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-md hover:border-indigo-200 transition-all duration-200 group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $page->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $page->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">{{ $page->title }}</h3>
            <p class="text-xs text-slate-400 font-mono mb-4">/{{ $page->slug }}</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pages.edit', $page) }}" class="flex-1 text-center py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                    Edit Content
                </a>
                <button type="button"
                    onclick="confirmSubmit('del-page-{{ $page->id }}', { title: 'Delete Page', message: 'Delete &ldquo;{{ $page->title }}&rdquo;? This cannot be undone.', confirmText: 'Delete', type: 'danger' })"
                    class="p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <form id="del-page-{{ $page->id }}" action="{{ route('admin.pages.destroy', $page) }}" method="POST" style="display:none;">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
