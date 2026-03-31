@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Newsletter Subscribers</h2>
            <p class="admin-page-subtitle">{{ $subscribers->total() }} total subscriber{{ $subscribers->total() !== 1 ? 's' : '' }}</p>
        </div>
    </div>

    @include('admin._alerts')

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">#</th>
                        <th class="admin-table-head-cell">Email</th>
                        <th class="admin-table-head-cell">Subscribed At</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($subscribers as $subscriber)
                    <tr class="admin-table-row">
                        <td class="admin-table-cell text-sm text-slate-500">{{ $subscriber->id }}</td>
                        <td class="admin-table-cell text-sm text-slate-700 fw-500">{{ $subscriber->email }}</td>
                        <td class="admin-table-cell text-sm text-slate-500">{{ $subscriber->created_at->format('d M Y, H:i') }}</td>
                        <td class="admin-table-actions">
                            <button type="button"
                                onclick="confirmSubmit('del-sub-{{ $subscriber->id }}', { title: 'Remove Subscriber', message: 'Remove {{ $subscriber->email }} from the list?', confirmText: 'Yes, Remove', type: 'danger' })"
                                class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 hover:bg-rose-50">
                                Remove
                            </button>
                            <form id="del-sub-{{ $subscriber->id }}" action="{{ route('admin.subscribers.destroy', $subscriber) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="admin-table-cell py-10 text-center text-slate-500">No subscribers yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $subscribers->links() }}</div>
</div>
@endsection
