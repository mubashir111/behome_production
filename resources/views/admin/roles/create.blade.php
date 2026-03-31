@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="pb-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Create New Role</h1>
            <p class="text-sm text-slate-500 mt-1">Define a new role with specific permissions for your administrative team.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Back to Roles</a>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Role Information</h2>
                <p class="admin-card-subtitle">Create a named role that can be assigned to administrators.</p>
            </div>
            
            <div class="admin-form-grid">
                <div class="admin-form-field col-span-2">
                    <label for="name" class="admin-form-label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="admin-form-input"
                           placeholder="e.g. Inventory Manager, Product Editor, etc">
                    <p class="text-xs text-slate-500 mt-2">Use a descriptive name that clearly indicates the role's responsibilities.</p>
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2 p-4 rounded-lg bg-indigo-50 border border-indigo-200">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-indigo-900">💡 Tip</p>
                            <p class="text-sm text-indigo-800 mt-1">After creating the role, you'll be able to assign specific permissions (view products, edit orders, manage customers, etc.) from the permissions manager.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn-primary">Create Role</button>
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection