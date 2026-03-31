@extends('layouts.admin')

@section('title', 'Product Reviews')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Product Reviews</h2>
            <p class="admin-page-subtitle">Moderate customer reviews across all products.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reviews, products, customers..." class="flex-1 min-w-[200px] px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white" />
        <select name="star" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Stars</option>
            @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" {{ request('star') == $i ? 'selected' : '' }}>{{ $i }} ★</option>
            @endfor
        </select>
        <button type="submit" class="admin-btn-primary">Filter</button>
        @if(request()->anyFilled(['search','star']))
        <a href="{{ route('admin.reviews.index') }}" class="admin-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Rating</th>
                        <th class="admin-table-head-cell">Product</th>
                        <th class="admin-table-head-cell">Customer</th>
                        <th class="admin-table-head-cell">Review</th>
                        <th class="admin-table-head-cell">Date</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($reviews as $review)
                    <tr class="admin-table-row">
                        <td class="admin-table-cell">
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->star ? 'text-yellow-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                @endfor
                            </div>
                        </td>
                        <td class="admin-table-cell">
                            <a href="{{ route('admin.products.show', $review->product_id) }}" class="text-sm font-medium text-slate-900 hover:text-indigo-600">
                                {{ $review->product->name ?? '—' }}
                            </a>
                        </td>
                        <td class="admin-table-cell">
                            <div class="flex items-center">
                                <div class="h-7 w-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 mr-2">
                                    {{ substr($review->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm text-slate-700">{{ $review->user->name ?? 'Guest' }}</span>
                            </div>
                        </td>
                        <td class="admin-table-cell max-w-xs">
                            <p class="text-sm text-slate-600 truncate">{{ $review->review ?: '—' }}</p>
                        </td>
                        <td class="admin-table-cell text-sm text-slate-500">
                            {{ $review->created_at->format('M d, Y') }}
                        </td>
                        <td class="admin-table-actions">
                            <a href="{{ route('admin.reviews.show', $review) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button type="button" onclick="confirmSubmit('del-review-{{ $review->id }}', { title: 'Delete Review', message: 'Are you sure you want to delete this review? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            <form id="del-review-{{ $review->id }}" method="POST" action="{{ route('admin.reviews.destroy', $review) }}" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="admin-table-cell py-12 text-center text-slate-400">No reviews found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
