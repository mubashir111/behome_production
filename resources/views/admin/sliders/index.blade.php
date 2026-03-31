@extends('layouts.admin')

@section('title', 'Hero Sliders')

@section('content')
@if(session('success'))
    <div class="glass border-l-4 border-emerald-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0 text-emerald-500 mr-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 transition-colors">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
@endif
@if(session('error'))
    <div class="glass border-l-4 border-rose-500 p-4 mb-6 rounded-2xl flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0 text-rose-500 mr-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 transition-colors">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>
    </div>
@endif

<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Hero Sliders</h2>
        <p class="text-slate-500 mt-1">Manage your homepage hero banner slides.</p>
    </div>
    <a href="{{ route('admin.sliders.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Slide
    </a>
</div>

<div class="glass p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="text-slate-400 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </span>
    <input id="sliders-search" type="text" placeholder="Search sliders…" class="flex-1 bg-transparent border-none outline-none focus:outline-none text-slate-900" />
</div>

{{-- Sliders Grid --}}
<div id="sliders-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($sliders as $slider)
    <div data-search-item class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Image Preview --}}
        <div class="relative h-44 bg-slate-100">
            <img src="{{ $slider->image }}" alt="{{ $slider->title }}" class="w-full h-full object-cover">
            <div class="absolute top-3 right-3">
                @if($slider->status == 5)
                    <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/90 backdrop-blur rounded-lg">Active</span>
                @else
                    <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-100/90 backdrop-blur rounded-lg">Inactive</span>
                @endif
            </div>
            @if($slider->badge_text)
            <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 text-xs font-bold text-amber-900 bg-amber-300/90 backdrop-blur rounded-lg">{{ $slider->badge_text }}</span>
            </div>
            @endif
        </div>
        {{-- Info --}}
        <div class="p-5">
            <h3 class="text-base font-bold text-slate-900 truncate">{{ $slider->title }}</h3>
            @if($slider->description)
            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $slider->description }}</p>
            @endif
            <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
                <span class="px-2 py-1 bg-slate-100 rounded-lg font-mono">{{ $slider->button_text ?? 'Shop Now' }}</span>
                @if($slider->link)
                <span class="px-2 py-1 bg-slate-100 rounded-lg font-mono truncate max-w-[140px]">{{ $slider->link }}</span>
                @endif
            </div>
            {{-- Actions --}}
            <div class="mt-4 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                {{-- Toggle Status --}}
                <form action="{{ route('admin.sliders.toggle-status', $slider) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ $slider->status == 5 ? 'text-slate-600 bg-slate-100 hover:bg-slate-200' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }}">
                        {{ $slider->status == 5 ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <a href="{{ route('admin.sliders.edit', $slider) }}" class="px-4 py-2 text-sm font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl transition-all">Edit</a>
                {{-- Delete: uses a separate hidden form triggered by JS to bypass admin JS interception --}}
                <button type="button"
                    onclick="confirmSubmit('delete-slider-{{ $slider->id }}', { title: 'Delete Slide', message: 'Are you sure you want to delete this slide? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })"
                    class="px-4 py-2 text-sm font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all">
                    Delete
                </button>
                <form id="delete-slider-{{ $slider->id }}" action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-24 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <p class="text-lg font-medium text-slate-900">No slides yet</p>
        <p class="text-slate-500 mt-1">Create your first hero slide to make the homepage dynamic.</p>
        <a href="{{ route('admin.sliders.create') }}" class="mt-4 inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-500 transition-all">Add First Slide</a>
    </div>
    @endforelse
</div>
@if($sliders->hasPages())
<div class="mt-6">{{ $sliders->links() }}</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('sliders-search', 'sliders-grid');</script>
@endpush
