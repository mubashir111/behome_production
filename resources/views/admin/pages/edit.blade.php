@extends('layouts.admin')
@section('title', 'Edit ' . $page->title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Edit: {{ $page->title }}</h2>
        <p class="text-slate-500 mt-1 text-sm font-mono">/{{ $page->slug }}</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="admin-btn-secondary">← Back</a>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data" style="width:100%">
@csrf
@method('PUT')

{{-- Common meta fields --}}
<div class="admin-card mb-6">
    <div class="admin-card-header"><h3 class="admin-card-title">Page Settings</h3></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Page Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                <span class="text-sm font-semibold text-slate-700">Page is Active (visible on frontend)</span>
            </label>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Description</label>
            <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
</div>

{{-- Contact page specific form --}}
@if($page->slug === 'contact')
    @php $s = $page->sections ?? []; @endphp

    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Contact Information</h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Office Address</label>
                <input type="text" name="address" value="{{ old('address', $s['address'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone Numbers <span class="text-xs text-slate-400">(one per line)</span></label>
                <textarea name="phones" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('phones', implode("\n", $s['phones'] ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Addresses <span class="text-xs text-slate-400">(one per line)</span></label>
                <textarea name="emails" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('emails', implode("\n", $s['emails'] ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Careers Email</label>
                <input type="email" name="careers_email" value="{{ old('careers_email', $s['careers_email'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Map Search Query</label>
                <input type="text" name="map_query" value="{{ old('map_query', $s['map_query'] ?? '') }}" placeholder="e.g. Westminster, London" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

{{-- Blog page: just a plain intro text --}}
@elseif($page->slug === 'blog')
    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Blog Page</h3></div>
        <p class="text-sm text-slate-500 mb-4">The blog post listing is managed separately. Here you can control the page heading and intro text shown above the posts.</p>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Intro / Tagline</label>
            <textarea name="content" rows="3" placeholder="e.g. Explore interior design inspiration, home decor trends, and expert tips." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $page->content) }}</textarea>
        </div>
    </div>

{{-- All other pages (about, privacy-policy, shipping-policy, returns-policy, faq, custom pages) --}}
@else
    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Page Content</h3></div>

        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

        {{-- Hidden textarea submitted with the form --}}
        <textarea name="content" id="content-hidden" style="display:none;">{{ old('content', $page->content) }}</textarea>

        {{-- Quill editor --}}
        <div id="quill-editor" style="min-height: 420px; font-size: 14px; background:#fff; border-radius: 0 0 8px 8px;"></div>

        <p class="text-xs text-slate-400 mt-2">Use the toolbar above to format text — headings, bold, links, lists, and more.</p>
    </div>
@endif

<div class="flex items-center gap-3">
    <button type="submit" class="admin-btn-primary px-6 py-2.5">Save Changes</button>
    <a href="{{ route('admin.pages.index') }}" class="admin-btn-secondary px-6 py-2.5">Cancel</a>
</div>

</form>

<style>
#quill-editor .ql-editor { min-height: 380px; font-size: 14px; line-height: 1.7; color: #1e293b; }
.ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: #e2e8f0; background: #f8fafc; }
.ql-container.ql-snow { border-color: #e2e8f0; border-radius: 0 0 8px 8px; }
</style>

@if(!in_array($page->slug, ['contact', 'blog']))
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
@endif

@endsection
