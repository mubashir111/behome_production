@extends('layouts.admin')

@section('title', 'Returns & Refunds')

@section('content')
@php
    use App\Enums\RefundStatus;

    $statusClasses = [5 => 'text-amber-700 bg-amber-50', 10 => 'text-emerald-700 bg-emerald-50', 15 => 'text-rose-700 bg-rose-50'];
    $statusLabels  = [5 => 'Pending', 10 => 'Accepted', 15 => 'Rejected'];

    $refundClasses = [
        RefundStatus::AWAITING_ITEM => 'text-amber-700 bg-amber-50',
        RefundStatus::ITEM_RECEIVED => 'text-indigo-700 bg-indigo-50',
        RefundStatus::REFUND_ISSUED => 'text-emerald-700 bg-emerald-50',
    ];
    $refundLabels = [
        RefundStatus::AWAITING_ITEM => '⏳ Awaiting Item',
        RefundStatus::ITEM_RECEIVED => '📦 Item Received',
        RefundStatus::REFUND_ISSUED => '✓ Refunded',
    ];

    $unviewedCount = $returns->getCollection()->whereNull('admin_viewed_at')->count();
@endphp
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Returns & Refunds</h2>
            <p class="admin-page-subtitle">Manage customer return and refund requests.</p>
        </div>
        @if($unviewedCount > 0)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 18px;background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:14px;">
            <span style="width:8px;height:8px;background:#f97316;border-radius:50%;animation:pulse-badge 2s infinite;flex-shrink:0;"></span>
            <span style="font-size:13px;font-weight:700;color:#c2410c;">{{ $unviewedCount }} new unreviewed request{{ $unviewedCount > 1 ? 's' : '' }}</span>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or customer name..." class="flex-1 min-w-[200px] px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white" />
        <select name="status" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Return Statuses</option>
            <option value="5"  {{ request('status') == 5  ? 'selected' : '' }}>Pending</option>
            <option value="10" {{ request('status') == 10 ? 'selected' : '' }}>Accepted</option>
            <option value="15" {{ request('status') == 15 ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="refund_status" class="px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Refund Stages</option>
            <option value="{{ RefundStatus::AWAITING_ITEM }}" {{ request('refund_status') == RefundStatus::AWAITING_ITEM ? 'selected' : '' }}>Awaiting Item</option>
            <option value="{{ RefundStatus::ITEM_RECEIVED }}" {{ request('refund_status') == RefundStatus::ITEM_RECEIVED ? 'selected' : '' }}>Item Received</option>
            <option value="{{ RefundStatus::REFUND_ISSUED }}" {{ request('refund_status') == RefundStatus::REFUND_ISSUED ? 'selected' : '' }}>Refunded</option>
        </select>
        <button type="submit" class="admin-btn-primary">Filter</button>
        @if(request()->anyFilled(['search','status','refund_status']))
        <a href="{{ route('admin.returns.index') }}" class="admin-btn-secondary">Clear</a>
        @endif
    </form>

    <div class="admin-table-card">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Order #</th>
                        <th class="admin-table-head-cell">Customer</th>
                        <th class="admin-table-head-cell">Reason</th>
                        <th class="admin-table-head-cell">Items</th>
                        <th class="admin-table-head-cell">Value</th>
                        <th class="admin-table-head-cell">Requested</th>
                        <th class="admin-table-head-cell">Return Status</th>
                        <th class="admin-table-head-cell">Refund Stage</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($returns as $return)
                    @php $isNew = !$return->admin_viewed_at; @endphp
                    <tr class="admin-table-row" style="{{ $isNew ? 'background:linear-gradient(90deg,#fff7ed 0%,#fff 55%);border-left:3px solid #f97316;' : '' }}">
                        <td class="admin-table-cell">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-slate-900">#{{ $return->order_serial_no }}</span>
                                @if($isNew)
                                    <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;background:#f97316;color:#fff;font-size:10px;font-weight:700;border-radius:100px;letter-spacing:0.05em;white-space:nowrap;">
                                        <span style="width:5px;height:5px;background:#fed7aa;border-radius:50%;display:inline-block;"></span>NEW
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="admin-table-cell">
                            <div class="flex items-center">
                                <div class="h-7 w-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 mr-2">
                                    {{ substr($return->user->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm text-slate-700">{{ $return->user->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="admin-table-cell text-sm text-slate-600">{{ $return->returnReason->title ?? '—' }}</td>
                        <td class="admin-table-cell text-sm text-slate-700">{{ $return->returnProducts->count() }}</td>
                        <td class="admin-table-cell text-sm font-medium text-slate-900">
                            {{ number_format($return->returnProducts->sum('return_price'), 2) }}
                        </td>
                        <td class="admin-table-cell">
                            <span class="text-sm text-slate-500">{{ $return->created_at->format('M d, Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $return->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="admin-table-cell">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $statusClasses[$return->status] ?? 'text-slate-700 bg-slate-100' }}">
                                {{ $statusLabels[$return->status] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="admin-table-cell">
                            @if($return->refund_status)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $refundClasses[$return->refund_status] ?? 'text-slate-700 bg-slate-100' }}">
                                    {{ $refundLabels[$return->refund_status] ?? '—' }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="admin-table-actions">
                            <a href="{{ route('admin.returns.show', $return) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="admin-table-cell py-12 text-center text-slate-400">No return requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
