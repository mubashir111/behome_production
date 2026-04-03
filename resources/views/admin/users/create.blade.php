@extends('layouts.admin')

@section('title', 'Add New Administrator')

@section('content')
<div class="pb-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Add New Administrator</h1>
            <p class="text-sm text-slate-500 mt-1">Create a new admin account with access permissions.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Back to List</a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6 admin-card">
        @csrf

        <div class="admin-card-header">
            <h2 class="admin-card-title">Basic Information</h2>
            <p class="admin-card-subtitle">Enter account details and contact info.</p>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-field">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Alex Johnson">
                @error('name') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="alex@example.com">
                @error('email') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 890">
                @error('phone') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Country Code</label>
                <input type="text" name="country_code" value="{{ old('country_code', '+1') }}" required placeholder="+1">
                @error('country_code') <p class="text-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="admin-card-header">
            <h2 class="admin-card-title">Security & Access</h2>
            <p class="admin-card-subtitle">Set password and role assignment.</p>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-field">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
                @error('password') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••">
            </div>
            <div class="admin-form-field">
                <label>Assign Role</label>
                <select name="roles[]" required>
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('roles.0') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('roles') <p class="text-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-form-field">
                <label>Account Status</label>
                <select name="status">
                    <option value="5" {{ old('status', '5') == '5' ? 'selected' : '' }}>Active</option>
                    <option value="10" {{ old('status') == '10' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Create Administrator</button>
        </div>
    </form>
</div>
@endsection