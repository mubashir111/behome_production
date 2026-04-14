@extends('layouts.admin')

@section('title', 'Send Notifications')

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Send Notifications</h2>
            <p class="admin-page-subtitle">Send messages and announcements to customers.</p>
        </div>
        <a href="{{ route('admin.user-notifications.create') }}" class="admin-btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Send Notification
        </a>
    </div>

    @include('admin._alerts')

    @if(session('success'))
        <div class="admin-alert-success">{{ session('success') }}</div>
    @endif

    <div class="admin-card" style="overflow:hidden;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Notification</th>
                    <th>Type</th>
                    <th>Recipient</th>
                    <th>Sent</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                <tr>
                    <td style="color:#94a3b8;font-size:12px;">{{ $n->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:{{ $n->color }}20;color:{{ $n->color }};">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div>
                                <p style="font-size:13px;font-weight:600;color:#0f172a;margin:0 0 2px;">{{ $n->title }}</p>
                                <p style="font-size:12px;color:#64748b;margin:0;">{{ Str::limit($n->body, 55) }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $typeColors = ['info'=>'#6366f1','success'=>'#10b981','warning'=>'#f59e0b','promo'=>'#3b82f6']; @endphp
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:{{ ($typeColors[$n->type] ?? '#6366f1') }}18;color:{{ $typeColors[$n->type] ?? '#6366f1' }};">
                            {{ ucfirst($n->type) }}
                        </span>
                    </td>
                    <td>
                        @if($n->user_id)
                            <div>
                                <p style="font-size:13px;font-weight:600;color:#0f172a;margin:0 0 1px;">{{ $n->user?->name ?? 'Deleted User' }}</p>
                                <p style="font-size:11px;color:#94a3b8;margin:0;">{{ $n->user?->email }}</p>
                            </div>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:#0f172a;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                                All Users
                            </span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $n->created_at->diffForHumans() }}</td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('admin.user-notifications.destroy', $n) }}" onsubmit="return confirm('Delete this notification?')">
                            @csrf @method('DELETE')
                            <button class="admin-btn-danger-sm">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div style="padding:60px 20px;text-align:center;">
                            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 12px;display:block;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p style="color:#94a3b8;font-size:14px;margin:0;">No notifications sent yet</p>
                            <a href="{{ route('admin.user-notifications.create') }}" style="display:inline-block;margin-top:12px;font-size:13px;color:#6366f1;font-weight:600;text-decoration:none;">Send your first notification →</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($notifications->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
