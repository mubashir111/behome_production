@extends('layouts.admin')

@section('title', 'Edit Administrator')

@section('content')
<div class="pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900 mb-2">Edit Administrator</h1>
            <p class="text-slate-500 mt-1">Update profile details and access levels for {{ $user->name }}.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="glass px-6 py-3 rounded-2xl flex items-center gap-2 text-slate-600 font-bold hover:bg-slate-50 transition-all duration-300 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6 admin-card">
        @csrf
        @method('PUT')

        <div class="admin-card-header">
            <h2 class="admin-card-title">Edit Administrator</h2>
            <p class="admin-card-subtitle">Update profile details and access levels.</p>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-field">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 890">
                @error('phone') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Country Code</label>
                <input type="text" name="country_code" value="{{ old('country_code', $user->country_code ?: '+1') }}" required>
                @error('country_code') <p class="text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="admin-card-header">
            <h3 class="admin-card-title">Security & Access</h3>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-field">
                <label>Password (optional)</label>
                <input type="password" name="password" placeholder="••••••••">
                @error('password') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="••••••••">
            </div>
            <div class="admin-form-field">
                <label>Assign Role</label>
                <select name="roles[]" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (old('roles.0') ?? $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('roles') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Account Status</label>
                <select name="status" required>
                    <option value="1" {{ old('status', $user->status) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $user->status) == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Save User Changes</button>
        </div>
    </form>
</div>
@endsection
