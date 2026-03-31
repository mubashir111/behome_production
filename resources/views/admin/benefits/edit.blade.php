@extends('layouts.admin')
@section('title', 'Edit Ticker Item')
@section('content')

<div class="mb-8 flex items-center gap-4">
    <a href="{{ route('admin.benefits.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Edit Ticker Item</h2>
        <p class="text-slate-500 mt-1">{{ $benefit->title }}</p>
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
    <form action="{{ route('admin.benefits.update', $benefit) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Current Icon</label>
            <img src="{{ $benefit->thumb }}" alt="{{ $benefit->title }}" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $benefit->title) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
            <input type="text" name="description" value="{{ old('description', $benefit->description) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Sort Order</label>
            <input type="number" name="sort" value="{{ old('sort', $benefit->sort) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span class="text-rose-500">*</span></label>
            <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white" required>
                <option value="5" {{ old('status', $benefit->status) == 5 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $benefit->status) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Replace Icon <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl shadow-indigo-200/50">
                Save Changes
            </button>
            <a href="{{ route('admin.benefits.index') }}" class="px-6 py-3 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-2xl font-semibold transition-all">Cancel</a>
        </div>
    </form>
</div>
@endsection
