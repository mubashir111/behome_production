@extends('layouts.admin')
@section('title', 'Add Promotion')
@section('content')

<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.promotions.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Add Promotion</h2>
        <p class="text-slate-500 mt-1">Create a new homepage promotion banner.</p>
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
    <form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" placeholder="e.g. Winter Collection Sale" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" placeholder="e.g. Up to 40% off">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Link URL</label>
            <input type="text" name="link" value="{{ old('link', '/shop') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" placeholder="/shop">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Type <span class="text-rose-500">*</span></label>
            <select name="type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white" required>
                <option value="">Select type</option>
                <option value="10" {{ old('type') == 10 ? 'selected' : '' }}>Homepage Left Banner (Tall, 660px)</option>
                <option value="5"  {{ old('type') == 5  ? 'selected' : '' }}>Homepage Right Banner (Stacked, up to 2)</option>
                <option value="1"  {{ old('type') == 1  ? 'selected' : '' }}>Hero Slider Card (Editorial feature)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span class="text-rose-500">*</span></label>
            <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white" required>
                <option value="5" {{ old('status') == 5 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Image <span class="text-rose-500">*</span></label>
            <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700" required>
            <p class="text-xs text-slate-400 mt-1.5">Recommended: 1200×400px for feature banner, 600×660px for big banner, 600×310px for small banner. Max 6MB.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50">
                Create Promotion
            </button>
            <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-2xl font-semibold transition-all">Cancel</a>
        </div>
    </form>
</div>
@endsection
