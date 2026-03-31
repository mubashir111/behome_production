@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('content')
<div class="pb-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900 mb-2">Edit Customer</h1>
            <p class="text-slate-500 mt-1">Update customer profile details.</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="glass px-6 py-3 rounded-2xl flex items-center gap-2 text-slate-600 font-bold hover:bg-slate-50 transition-all duration-300 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </a>
    </div>

    <div class="admin-card">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2" required>
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2" required>
                    @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2">
                    @error('phone')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Country Code</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $customer->country_code ?? '+1') }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2" required>
                    @error('country_code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2">
                    <p class="text-xs text-slate-500 mt-1">Leave blank to keep existing password.</p>
                    @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2">
                        <option value="1" {{ old('status', $customer->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $customer->status) != 1 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" value="{{ old('username', $customer->username) }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2">
                    @error('username')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="admin-form-actions">
                <a href="{{ route('admin.customers.index') }}" class="admin-btn-secondary">Discard</a>
                <button type="submit" class="admin-btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection
