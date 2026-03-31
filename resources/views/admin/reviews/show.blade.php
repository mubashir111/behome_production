@extends('layouts.admin')

@section('title', 'Review Detail')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <a href="{{ route('admin.reviews.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 mb-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Reviews
            </a>
            <h2 class="admin-page-title">Review Detail</h2>
        </div>
        <button type="button" onclick="confirmSubmit('del-review-detail', { title: 'Delete Review', message: 'Are you sure you want to permanently delete this review? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="px-6 py-3 bg-gradient-to-r from-rose-600 to-rose-700 text-white rounded-2xl font-bold hover:from-rose-500 hover:to-rose-600 transition-all shadow-xl shadow-rose-200/50">
            Delete Review
        </button>
        <form id="del-review-detail" method="POST" action="{{ route('admin.reviews.destroy', $review) }}" style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Review Content --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass rounded-2xl p-6">
                <div class="flex items-center gap-1 mb-4">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= $review->star ? 'text-yellow-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                    <span class="ml-2 text-sm font-medium text-slate-700">{{ $review->star }} / 5</span>
                </div>
                <p class="text-slate-700 text-base leading-relaxed">{{ $review->review ?: 'No written review provided.' }}</p>
                @if($review->images)
                <div class="flex flex-wrap gap-3 mt-4">
                    @foreach($review->images as $img)
                    <a href="{{ $img }}" target="_blank">
                        <img src="{{ $img }}" class="h-20 w-20 rounded-xl object-cover border border-slate-200 hover:opacity-80 transition" />
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Meta --}}
        <div class="space-y-4">
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Product</h3>
                @if($review->product)
                <a href="{{ route('admin.products.show', $review->product_id) }}" class="block text-sm font-medium text-slate-900 hover:text-indigo-600">
                    {{ $review->product->name }}
                </a>
                @else
                <p class="text-sm text-slate-400">Product not found</p>
                @endif
            </div>
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Reviewer</h3>
                <p class="text-sm font-medium text-slate-900">{{ $review->user->name ?? 'Guest' }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $review->user->email ?? '' }}</p>
            </div>
            <div class="glass rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Date</h3>
                <p class="text-sm text-slate-700">{{ $review->created_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
