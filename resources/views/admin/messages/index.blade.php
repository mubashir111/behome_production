@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Contact Messages</h2>
            <p class="admin-page-subtitle">Messages submitted via the contact form.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or message..." class="flex-1 min-w-[200px] px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white" />
        <select name="status" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Messages</option>
            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
            <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Read</option>
        </select>
        <button type="submit" class="admin-btn-primary">Filter</button>
        @if(request()->anyFilled(['search','status']))
        <a href="{{ route('admin.messages.index') }}" class="admin-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Name</th>
                        <th class="admin-table-head-cell">Email</th>
                        <th class="admin-table-head-cell">Message</th>
                        <th class="admin-table-head-cell">Date</th>
                        <th class="admin-table-head-cell">Status</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($messages as $msg)
                    <tr class="admin-table-row">
                        <td class="admin-table-cell">
                            <span class="text-sm font-semibold text-slate-900 {{ !$msg->is_read ? 'font-bold' : '' }}">{{ $msg->name }}</span>
                        </td>
                        <td class="admin-table-cell text-sm text-slate-600">{{ $msg->email }}</td>
                        <td class="admin-table-cell max-w-xs">
                            <p class="text-sm text-slate-600 truncate">{{ $msg->message }}</p>
                        </td>
                        <td class="admin-table-cell text-sm text-slate-500">{{ $msg->created_at->format('M d, Y') }}</td>
                        <td class="admin-table-cell">
                            @if($msg->is_read)
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg text-slate-500 bg-slate-100">Read</span>
                            @else
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg text-indigo-700 bg-indigo-50">Unread</span>
                            @endif
                        </td>
                        <td class="admin-table-actions">
                            <a href="{{ route('admin.messages.show', $msg) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button type="button" onclick="confirmSubmit('del-msg-{{ $msg->id }}', { title: 'Delete Message', message: 'Are you sure you want to delete this message? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            <form id="del-msg-{{ $msg->id }}" method="POST" action="{{ route('admin.messages.destroy', $msg) }}" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="admin-table-cell py-12 text-center text-slate-400">No messages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
