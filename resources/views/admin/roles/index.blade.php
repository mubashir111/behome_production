@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold font-outfit text-slate-900">Roles & Permissions</h2>
        <p class="text-slate-500 mt-1">Define access levels and permission groups for your administrators.</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="admin-btn-primary">
        + Create Role
    </a>
</div>

@if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        {{ session('success') }}
    </div>
@endif

<!-- Roles Table -->
<div class="glass rounded-[2rem] overflow-hidden border border-slate-200 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="roles-search" type="text" placeholder="Search roles…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
    </div>
    <div class="overflow-x-auto">
        <table id="roles-table" class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-8 py-5 text-sm font-bold text-slate-700 uppercase tracking-wider">Role Name</th>
                    <th class="px-8 py-5 text-sm font-bold text-slate-700 uppercase tracking-wider">Users Count</th>
                    <th class="px-8 py-5 text-sm font-bold text-slate-700 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($roles as $role)
                <tr class="hover:bg-indigo-50/30 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                {{ substr($role->name, 0, 1) }}
                            </div>
                            <span class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider">
                            {{ $role->users_count ?? 0 }} Users
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.permissions.edit', $role->id) }}" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" title="Manage Permissions">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V22m0-19.056c1.11 0 2.086.67 2.483 1.632l.082.204M12 2.944c-1.11 0-2.086.67-2.483 1.632l-.082.204m11.691 10.272a11.956 11.956 0 01-2.108 3.96M12 22a11.956 11.956 0 01-9.583-4.744M12 22V12m4.517 7.406c-.23.64-.52 1.246-.867 1.815M12 12H7m5 0v10"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.roles.edit', $role) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Edit Role">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @if($role->id > 5) {{-- Prevent deleting system roles --}}
                            <button type="button" onclick="confirmSubmit('del-role-{{ $role->id }}', { title: 'Delete Role', message: 'Deleting this role will affect all users assigned to it. Are you sure you want to continue?', confirmText: 'Yes, Delete', type: 'danger' })" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Delete Role">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            <form id="del-role-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-8 py-12 text-center text-slate-500">
                        No roles defined yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($roles->hasPages())
<div class="mt-8">
    {{ $roles->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('roles-search', 'roles-table');</script>
@endpush