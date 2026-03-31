@extends('layouts.admin')
@section('title', 'Add FAQ Item')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Add FAQ Item</h2>
        <p class="text-slate-500 mt-1 text-sm">Add a new frequently asked question.</p>
    </div>
    <a href="{{ route('admin.faq.index') }}" class="admin-btn-secondary">← Back</a>
</div>

@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.faq.store') }}" method="POST" class="max-w-2xl">
@csrf

<div class="admin-card space-y-4">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category <span class="text-rose-500">*</span></label>
        <select name="category" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question <span class="text-rose-500">*</span></label>
        <input type="text" name="question" value="{{ old('question') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Answer <span class="text-rose-500">*</span></label>
        <textarea name="answer" rows="5" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('answer') }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                <span class="text-sm font-semibold text-slate-700">Active</span>
            </label>
        </div>
    </div>
</div>

<div class="flex gap-3 mt-4">
    <button type="submit" class="admin-btn-primary px-6 py-2.5">Create FAQ Item</button>
    <a href="{{ route('admin.faq.index') }}" class="admin-btn-secondary px-6 py-2.5">Cancel</a>
</div>
</form>

@endsection
