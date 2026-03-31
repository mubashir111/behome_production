@extends('layouts.admin')
@section('title', 'Edit Promotion')
@section('content')

<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.promotions.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Edit Promotion</h2>
        <p class="text-slate-500 mt-1">{{ $promotion->name }}</p>
    </div>
</div>

@if($errors->any())
<div class="glass border-l-4 border-rose-500 p-4 mb-6 rounded-2xl">
    <ul class="list-disc list-inside text-sm text-rose-700 space-y-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 max-w-2xl">
    <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $promotion->name) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $promotion->subtitle) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Link URL</label>
            <input type="text" name="link" value="{{ old('link', $promotion->link) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Type <span class="text-rose-500">*</span></label>
            <select name="type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white" required>
                <option value="10" {{ old('type', $promotion->type) == 10 ? 'selected' : '' }}>Homepage Left Banner (Tall, 660px)</option>
                <option value="5"  {{ old('type', $promotion->type) == 5  ? 'selected' : '' }}>Homepage Right Banner (Stacked, up to 2)</option>
                <option value="1"  {{ old('type', $promotion->type) == 1  ? 'selected' : '' }}>Hero Slider Card (Editorial feature)</option>
                <option value="15" {{ old('type', $promotion->type) == 15 ? 'selected' : '' }}>Feature Section Banner (Split screen with products)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span class="text-rose-500">*</span></label>
            <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white" required>
                <option value="5" {{ old('status', $promotion->status) == 5 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $promotion->status) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Replace Image <span class="text-slate-400 font-normal">(optional)</span></label>
            @include('admin.partials.image_uploader', [
                'inputName'      => 'image',
                'label'          => 'Banner Image',
                'aspectRatio'    => null,
                'currentImageUrl'=> $promotion->cover,
                'outputWidth'    => 1200,
                'outputHeight'   => 660,
            ])
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50">
                Save Changes
            </button>
            <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-2xl font-semibold transition-all">Cancel</a>
        </div>
    </form>
</div>
@endsection
