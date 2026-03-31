@extends('layouts.admin')

@section('title', 'Admin Users')

@section('content')
<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Admin Users</h2>
        <p class="text-slate-500 mt-1">Manage administrative access and roles.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="glass px-6 py-3 rounded-2xl flex items-center gap-2 text-indigo-600 font-bold hover:bg-indigo-600 hover:text-white transition-all duration-300 shadow-sm border border-indigo-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Administrator
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

<!-- Users Grid -->
<div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input id="users-search" type="text" placeholder="Search users…" style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
</div>
<div id="users-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($admins as $admin)
    <div data-search-item class="glass p-6 rounded-[2.5rem] hover:shadow-xl transition-all duration-300 group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110 duration-500"></div>
        
        <div class="relative flex items-start justify-between mb-6">
            <div class="flex items-center">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-indigo-200">
                    {{ substr($admin->name, 0, 1) }}
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold font-outfit text-slate-900">{{ $admin->name }}</h3>
                    <p class="text-xs font-medium px-2 py-1 rounded-lg bg-indigo-50 text-indigo-600 inline-block mt-1">
                        {{ $admin->getRoleNames()->first() ?? 'No Role' }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-1">
                <a href="{{ route('admin.users.edit', $admin) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
                @if(auth()->id() !== $admin->id && $admin->id !== 1)
                <button type="button" onclick="confirmSubmit('del-user-{{ $admin->id }}', { title: 'Delete Admin User', message: 'Are you sure you want to delete this admin user? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-slate-50 rounded-xl transition-all" title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                <form id="del-user-{{ $admin->id }}" action="{{ route('admin.users.destroy', $admin) }}" method="POST" style="display:none;">
                    @csrf @method('DELETE')
                </form>
                @endif
            </div>
        </div>

        <div class="space-y-3 relative">
            <div class="flex items-center text-sm text-slate-500">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ $admin->email }}
            </div>
            <div class="flex items-center text-sm text-slate-500">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                {{ $admin->phone ?: 'No phone' }}
            </div>
            <div class="flex items-center text-sm text-slate-500">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Status: 
                <span class="ml-1 font-semibold {{ $admin->status == 1 ? 'text-emerald-500' : 'text-slate-400' }}">
                    {{ $admin->status == 1 ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Joined</span>
            <span class="text-xs font-semibold text-slate-600">{{ $admin->created_at->format('M d, Y') }}</span>
        </div>
    </div>
    @empty
    <div class="col-span-full py-12 text-center text-slate-500 glass rounded-[2.5rem] border border-slate-200">
        <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-4.744M9 3.941a4.973 4.973 0 013.593 1.412a4.973 4.973 0 011.412 3.593c0 1.053-.326 2.03-.884 2.834a4.973 4.973 0 01-2.834.884c-1.053 0-2.03-.326-2.834-.884a4.973 4.973 0 01-.884-2.834c0-2.747 2.226-4.973 4.973-4.973z"></path>
            </svg>
            <p class="text-lg font-medium text-slate-900">No administrators found</p>
        </div>
    </div>
    @endforelse
</div>

@if($admins->hasPages())
<div class="mt-8">
    {{ $admins->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>adminSearch('users-search', 'users-grid');</script>
@endpush
