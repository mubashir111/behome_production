@extends('layouts.admin')

@section('title', 'Edit Post')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Edit Post</h2>
        <p class="text-slate-500 mt-1 font-mono text-sm">/blog/{{ $blog->slug }}</p>
    </div>
    <a href="{{ route('admin.blog.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all">← Back</a>
</div>

@if($errors->any())
<div class="glass border-l-4 border-rose-500 p-4 mb-6 rounded-2xl">
    <ul class="list-disc list-inside text-sm text-rose-700 space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.blog.update', $blog) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $blog->title) }}" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 text-lg font-medium">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 resize-y">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Content</label>
                <textarea name="content" rows="18"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 font-mono text-sm resize-y">{{ old('content', $blog->content) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">HTML is supported.</p>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-5">
            <h3 class="font-semibold text-slate-800">SEO Settings</h3>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Meta Description</label>
                <textarea name="meta_description" rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 resize-none">{{ old('meta_description', $blog->meta_description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Publish --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Publish</h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ $blog->is_published ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                <label for="is_published" class="text-sm font-medium text-slate-700">Published</label>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Publish Date</label>
                <input type="datetime-local" name="published_at"
                    value="{{ old('published_at', $blog->published_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 text-sm">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-5 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all">
                    Save Changes
                </button>
                <a href="{{ route('admin.blog.index') }}" class="px-4 py-3 text-slate-600 bg-slate-100 rounded-xl font-semibold hover:bg-slate-200 transition-all">Cancel</a>
            </div>
        </div>

        {{-- Cover Image --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Cover Image</h3>
            @if($blog->cover_image)
                <img src="{{ $blog->cover_image }}" class="w-full rounded-xl object-cover h-32" alt="">
                <p class="text-xs text-slate-400">Upload a new image to replace the current one.</p>
            @endif
            <input type="file" name="cover_image" accept="image/*"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-xl p-2">
        </div>

        {{-- Details --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Details</h3>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                <input type="text" name="category" value="{{ old('category', $blog->category) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Author</label>
                <input type="text" name="author" value="{{ old('author', $blog->author) }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
            <div class="pt-2 border-t border-slate-100 text-xs text-slate-400 space-y-1">
                <p>Views: {{ number_format($blog->views) }}</p>
                <p>Created: {{ $blog->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
