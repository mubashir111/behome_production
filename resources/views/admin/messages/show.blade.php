@extends('layouts.admin')

@section('title', 'Message from {{ $message->name }}')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <a href="{{ route('admin.messages.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 mb-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Messages
            </a>
            <h2 class="admin-page-title">Message from {{ $message->name }}</h2>
        </div>
        <button type="button" onclick="confirmSubmit('del-message-detail', { title: 'Delete Message', message: 'Are you sure you want to permanently delete this message? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-sm font-medium hover:bg-rose-700 transition-all">
            Delete
        </button>
        <form id="del-message-detail" method="POST" action="{{ route('admin.messages.destroy', $message) }}" style="display:none;">
            @csrf @method('DELETE')
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Message body --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Message</h3>
                <p class="text-slate-700 text-base leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
            </div>
        </div>

        {{-- Sidebar meta --}}
        <div class="space-y-4">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">From</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $message->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                    <a href="mailto:{{ $message->email }}" class="text-sm text-indigo-600 hover:underline">{{ $message->email }}</a>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Received</p>
                    <p class="text-sm text-slate-700">{{ $message->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status</p>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $message->is_read ? 'text-slate-500 bg-slate-100' : 'text-indigo-700 bg-indigo-50' }}">
                        {{ $message->is_read ? 'Read' : 'Unread' }}
                    </span>
                </div>
            </div>
            <a href="mailto:{{ $message->email }}" class="w-full px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Reply via Email
            </a>
        </div>
    </div>
</div>
@endsection
