@extends('layouts.admin')

@section('title', 'Blog Comments')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Blog Comments</h2>
            <p class="admin-page-subtitle">{{ $comments->total() }} total comment{{ $comments->total() !== 1 ? 's' : '' }}</p>
        </div>
    </div>

    @include('admin._alerts')

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Post</th>
                        <th class="admin-table-head-cell">Author</th>
                        <th class="admin-table-head-cell">Comment</th>
                        <th class="admin-table-head-cell">Status</th>
                        <th class="admin-table-head-cell">Date</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($comments as $comment)
                    <tr class="admin-table-row">
                        <td class="admin-table-cell text-sm text-slate-700" style="max-width:160px;">
                            <span class="truncate block" title="{{ $comment->post?->title }}">{{ Str::limit($comment->post?->title, 30) }}</span>
                        </td>
                        <td class="admin-table-cell text-sm">
                            <p class="text-slate-700 fw-500 mb-0">{{ $comment->name }}</p>
                            <p class="text-slate-400 text-xs mb-0">{{ $comment->email }}</p>
                        </td>
                        <td class="admin-table-cell text-sm text-slate-600" style="max-width:280px;">
                            {{ Str::limit($comment->comment, 80) }}
                        </td>
                        <td class="admin-table-cell">
                            @if($comment->is_approved)
                                <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded">Approved</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded">Pending</span>
                            @endif
                        </td>
                        <td class="admin-table-cell text-sm text-slate-500">{{ $comment->created_at->format('d M Y') }}</td>
                        <td class="admin-table-actions space-x-1">
                            <form action="{{ route('admin.blog-comments.approve', $comment) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="admin-btn-secondary py-2 px-3 text-xs">
                                    {{ $comment->is_approved ? 'Unapprove' : 'Approve' }}
                                </button>
                            </form>
                            <button type="button"
                                onclick="confirmSubmit('del-comment-{{ $comment->id }}', { title: 'Delete Comment', message: 'Delete this comment permanently?', confirmText: 'Yes, Delete', type: 'danger' })"
                                class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 hover:bg-rose-50">
                                Delete
                            </button>
                            <form id="del-comment-{{ $comment->id }}" action="{{ route('admin.blog-comments.destroy', $comment) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="admin-table-cell py-10 text-center text-slate-500">No comments yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $comments->links() }}</div>
</div>
@endsection
