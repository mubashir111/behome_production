@extends('layouts.admin')
@section('title', 'Static Pages')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Static Pages</h2>
        <p class="text-slate-500 mt-1 text-sm">Manage the content of your website's static pages.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

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
        </div>
    </div>
    @endforeach
</div>

@endsection
