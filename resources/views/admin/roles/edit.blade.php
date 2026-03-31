@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="pb-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Edit Role: {{ $role->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Update the role name and manage its permissions.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Back to Roles</a>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Role Details</h2>
                <p class="admin-card-subtitle">Modify the role name. Changes will affect all administrators with this role.</p>
            </div>
            
            <div class="admin-form-grid">
                <div class="admin-form-field col-span-2">
                    <label for="name" class="admin-form-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
                           class="admin-form-input"
                           placeholder="e.g. Inventory Manager">
                    <p class="text-xs text-slate-500 mt-2">Updating the name will affect all administrators assigned to this role.</p>
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2 p-4 rounded-lg bg-indigo-50 border border-indigo-200">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex gap-3 flex-1">
                            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-indigo-900">🔐 Manage Permissions</p>
                                <p class="text-sm text-indigo-800 mt-1">To assign specific permissions to this role, click the button on the right to access the permissions manager.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.permissions.edit', $role->id) }}" class="admin-btn-primary text-sm flex-shrink-0">
                            Open Permissions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn-primary">Update Role</button>
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection