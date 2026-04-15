@extends('layouts.admin')
@section('title', 'Create Page')

@section('content')
<div class="max-w-[800px] mx-auto pb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Create New Page</h2>
            <p class="text-slate-500 mt-1 text-sm">Add a new static page to your website.</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">← Back</a>
    </div>

    @include('admin._alerts')
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.pages.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Page Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Slug <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(lowercase, hyphens only)</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="e.g. shipping-policy"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description') }}"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
                <textarea name="content" id="content-hidden" style="display:none;">{{ old('content') }}</textarea>
                <div id="quill-editor" style="min-height:300px; font-size:14px; background:#fff; border-radius:0 0 8px 8px;"></div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                    <span class="text-sm font-semibold text-slate-700">Active (visible on frontend)</span>
                </label>
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all">
                    Create Page
                </button>
            </div>
        </div>
    </form>
</div>
<style>
#quill-editor .ql-editor { min-height: 260px; font-size: 14px; line-height: 1.7; color: #1e293b; }
.ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: #e2e8f0; background: #f8fafc; }
.ql-container.ql-snow { border-color: #e2e8f0; border-radius: 0 0 8px 8px; }
</style>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, false] }],
                [{ font: [] }, { size: ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    var existing = document.getElementById('content-hidden').value;
    if (existing) {
        quill.clipboard.dangerouslyPasteHTML(existing);
    }

    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('content-hidden').value = quill.root.innerHTML;
    });
})();
</script>
@endsection
