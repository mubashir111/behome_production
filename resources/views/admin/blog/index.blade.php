@extends('layouts.admin')

@section('title', 'Blog Posts')

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
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Blog Posts</h2>
        <p class="text-slate-500 mt-1">Manage your blog content and articles.</p>
    </div>
    <a href="{{ route('admin.blog.create') }}" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Post
    </a>
</div>
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="blog-search" type="text" placeholder="Search posts…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
    </div>
    <table id="blog-table" class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Post</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600 hidden md:table-cell">Category</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600 hidden lg:table-cell">Author</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600 hidden lg:table-cell">Views</th>
                <th class="text-left px-6 py-4 font-semibold text-slate-600">Status</th>
                <th class="text-right px-6 py-4 font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($posts as $post)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($post->cover_image)
                            <img src="{{ $post->cover_image }}" class="w-12 h-10 rounded-lg object-cover flex-shrink-0" alt="">
                        @else
                            <div class="w-12 h-10 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-900 line-clamp-1">{{ $post->title }}</p>
                            <p class="text-xs text-slate-400 font-mono">/blog/{{ $post->slug }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 hidden md:table-cell">
                    @if($post->category)
                        <span class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-lg">{{ $post->category }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-600 hidden lg:table-cell">{{ $post->author ?? '—' }}</td>
                <td class="px-6 py-4 text-slate-600 hidden lg:table-cell">{{ number_format($post->views) }}</td>
                <td class="px-6 py-4">
                    @if($post->is_published)
                        <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-lg">Published</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-100 rounded-lg">Draft</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <form action="{{ route('admin.blog.toggle-publish', $post) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $post->is_published ? 'text-slate-600 bg-slate-100 hover:bg-slate-200' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100' }}">
                                {{ $post->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.blog.edit', $post) }}" class="px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-all">Edit</a>
                        <button type="button"
                            onclick="confirmSubmit('delete-blog-{{ $post->id }}', { title: 'Delete Post', message: 'Are you sure you want to delete this post?', confirmText: 'Yes, Delete', type: 'danger' })"
                            class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all">
                            Delete
                        </button>
                        <form id="delete-blog-{{ $post->id }}" action="{{ route('admin.blog.destroy', $post) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-20 text-center">
                    <svg class="w-14 h-14 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-lg font-medium text-slate-900">No blog posts yet</p>
                    <p class="text-slate-500 mt-1">Create your first post to start your blog.</p>
                    <a href="{{ route('admin.blog.create') }}" class="mt-4 inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-500 transition-all">Write First Post</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($posts->hasPages())
<div class="mt-6">{{ $posts->links() }}</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('blog-search', 'blog-table');</script>
@endpush
