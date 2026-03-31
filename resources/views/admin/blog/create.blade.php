@extends('layouts.admin')

@section('title', 'New Blog Post')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">New Blog Post</h2>
        <p class="text-slate-500 mt-1">Write and publish a new article.</p>
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

<form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Enter post title"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 text-lg font-medium">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Excerpt <span class="text-slate-400 font-normal">(short summary shown in listing)</span></label>
                <textarea name="excerpt" rows="3" placeholder="Brief description of this post..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 resize-y">{{ old('excerpt') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Content</label>
                <textarea name="content" id="post-content" rows="18" placeholder="Write your blog post content here..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 font-mono text-sm resize-y">{{ old('content') }}</textarea>
                <p class="text-xs text-slate-400 mt-1">HTML is supported.</p>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-5">
            <h3 class="font-semibold text-slate-800">SEO Settings</h3>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Meta Title <span class="text-slate-400 font-normal">(defaults to post title)</span></label>
                <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="SEO title"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Meta Description</label>
                <textarea name="meta_description" rows="2" placeholder="SEO description (max 160 characters)"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 resize-none">{{ old('meta_description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Publish --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Publish</h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                <label for="is_published" class="text-sm font-medium text-slate-700">Publish immediately</label>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Publish Date <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 text-sm">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-5 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all">
                    Save Post
                </button>
                <a href="{{ route('admin.blog.index') }}" class="px-4 py-3 text-slate-600 bg-slate-100 rounded-xl font-semibold hover:bg-slate-200 transition-all">Cancel</a>
            </div>
        </div>

        {{-- Cover Image --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Cover Image</h3>
            <input type="file" name="cover_image" accept="image/*"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-xl p-2">
            <p class="text-xs text-slate-400">Recommended: 1200×630px. Max 4MB.</p>
        </div>

        {{-- Metadata --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="font-semibold text-slate-800">Details</h3>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Interior Design"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Author</label>
                <input type="text" name="author" value="{{ old('author') }}" placeholder="Author name"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900">
            </div>
        </div>
    </div>
</div>
</form>
@endsection
