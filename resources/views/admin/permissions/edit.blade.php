@extends('layouts.admin')

@section('title', 'Manage Permissions')

@section('content')
<div class="pb-10">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Manage Permissions</h1>
            <p class="text-sm text-slate-500 mt-1">Configure granular access levels for the <span class="font-bold text-indigo-600">{{ $role->name }}</span> role.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Back to Roles</a>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.permissions.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @php
                $groupedPermissions = [];
                foreach($permissions as $permission) {
                    $parts = explode('_', $permission->name);
                    $groupName = count($parts) > 1 ? $parts[0] : 'general';
                    $groupedPermissions[$groupName][] = $permission;
                }
            @endphp

            @foreach($groupedPermissions as $group => $items)
            <div class="admin-card">
                <div class="admin-card-header pb-4 mb-4 border-b border-slate-200">
                    <h3 class="admin-card-title capitalize flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        {{ str_replace('-', ' ', $group) }}
                    </h3>
                </div>
                
                <div class="space-y-3">
                    @foreach($items as $item)
                    <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-indigo-50 cursor-pointer transition-all group border border-transparent hover:border-indigo-200">
                        <div class="relative flex items-center flex-shrink-0">
                            <input type="checkbox" name="permissions[]" value="{{ $item->id }}" 
                                   {{ in_array($item->id, $rolePermissions) ? 'checked' : '' }}
                                   class="peer hidden">
                            <div class="w-5 h-5 rounded border-2 border-slate-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 flex items-center justify-center transition-all bg-white">
                                <svg class="w-3 h-3 text-white scale-0 peer-checked:scale-100 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-indigo-900 transition-colors">
                            {{ str_replace([$group . '_', '-', '_'], ['', ' ', ' '], $item->name) ?: 'Access' }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Update Permissions</button>
        </div>
    </form>
</div>
@endsection
