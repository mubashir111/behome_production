@extends('layouts.admin')

@section('title', 'Return #' . $return->order_serial_no)

@section('content')
@php
    use App\Enums\ReturnOrderStatus;
    use App\Enums\RefundStatus;

    $returnStatusLabels = [5 => 'Pending', 10 => 'Accepted', 15 => 'Rejected'];
    $returnStatusClasses = [
        5  => 'text-amber-700 bg-amber-50 border-amber-200',
        10 => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        15 => 'text-rose-700 bg-rose-50 border-rose-200',
    ];

    $isPending  = $return->status === ReturnOrderStatus::PENDING;
    $isAccepted = $return->status === ReturnOrderStatus::ACCEPT;
    $isRejected = $return->status === ReturnOrderStatus::REJECTED;

    $refundStatus   = $return->refund_status;
    $isAwaiting     = $refundStatus === RefundStatus::AWAITING_ITEM;
    $isItemReceived = $refundStatus === RefundStatus::ITEM_RECEIVED;
    $isRefunded     = $refundStatus === RefundStatus::REFUND_ISSUED;

    $totalRefund = $return->returnProducts->sum('return_price');

    // 4-step pipeline definition
    $steps = [
        ['label' => 'Request Submitted',  'sub' => 'Customer raised a return request',           'done' => true],
        ['label' => 'Request Accepted',   'sub' => 'Admin reviewed and approved the return',      'done' => $isAccepted || $isRefunded || $isAwaiting || $isItemReceived],
        ['label' => 'Item Received',      'sub' => 'Item shipped back and received by warehouse', 'done' => $isItemReceived || $isRefunded],
        ['label' => 'Refund Issued',      'sub' => 'Balance credited to customer wallet',         'done' => $isRefunded],
    ];
    if ($isRejected) {
        $steps[1] = ['label' => 'Request Rejected', 'sub' => 'Return request was not approved', 'done' => false, 'rejected' => true];
        $steps[2] = ['label' => 'Item Received',    'sub' => '—', 'done' => false, 'skipped' => true];
        $steps[3] = ['label' => 'Refund Issued',    'sub' => '—', 'done' => false, 'skipped' => true];
    }
@endphp

<div class="admin-page">

    {{-- Header --}}
    <div class="admin-page-header">
        <div>
            <a href="{{ route('admin.returns.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 mb-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Returns
            </a>
            <h2 class="admin-page-title">Return Request — Order #{{ $return->order_serial_no }}</h2>
            <p class="admin-page-subtitle">Submitted {{ $return->created_at->format('M d, Y \a\t g:i A') }} · {{ $return->created_at->diffForHumans() }}</p>
        </div>
        <span class="px-3 py-1.5 text-sm font-semibold rounded-xl border {{ $returnStatusClasses[$return->status] ?? 'text-slate-600 bg-slate-50 border-slate-200' }}">
            {{ $returnStatusLabels[$return->status] ?? 'Unknown' }}
        </span>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- ── 4-step Progress Tracker ────────────────────────────────── --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-6">
        <h3 class="text-sm font-bold text-slate-700 mb-5">Workflow Progress</h3>
        <div class="flex items-start gap-0">
            @foreach($steps as $i => $step)
            @php
                $isLast    = $i === count($steps) - 1;
                $rejected  = $step['rejected'] ?? false;
                $skipped   = $step['skipped'] ?? false;
                $done      = $step['done'] && !$rejected && !$skipped;
            @endphp
            <div class="flex-1 flex flex-col items-center relative">
                {{-- Connector line --}}
                @if(!$isLast)
                <div class="absolute top-4 left-1/2 w-full h-0.5 {{ $done ? 'bg-emerald-400' : 'bg-slate-200' }}" style="z-index:0;"></div>
                @endif

                {{-- Circle --}}
                <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mb-2 ring-2
                    {{ $rejected  ? 'bg-rose-100 text-rose-600 ring-rose-300' :
                       ($skipped  ? 'bg-slate-100 text-slate-400 ring-slate-200' :
                       ($done     ? 'bg-emerald-500 text-white ring-emerald-300' :
                                    'bg-white text-slate-400 ring-slate-200')) }}">
                    @if($rejected)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($done)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>

                {{-- Label --}}
                <p class="text-xs font-semibold text-center px-1
                    {{ $rejected ? 'text-rose-600' : ($done ? 'text-emerald-700' : 'text-slate-400') }}">
                    {{ $step['label'] }}
                </p>
                @if(!$skipped)
                <p class="text-xs text-slate-400 text-center px-1 mt-0.5 leading-tight hidden md:block">{{ $step['sub'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: Items + Note ──────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Return Items --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Return Items</h3>
                    <span class="text-sm text-slate-400">{{ $return->returnProducts->count() }} item(s)</span>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/60">
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Return Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($return->returnProducts as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $item->product->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->quantity ?? 1 }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ number_format($item->return_price, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">No items listed.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/60 border-t border-slate-200">
                            <td colspan="2" class="px-6 py-4 text-sm font-semibold text-slate-700">Total Refund</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ number_format($totalRefund, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Customer Note --}}
            @if($return->note)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Customer Note</p>
                <p class="text-sm text-slate-700 leading-relaxed">{{ $return->note }}</p>
            </div>
            @endif

            {{-- Attached Images --}}
            @if($return->images && count($return->images))
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Attached Images</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($return->images as $img)
                    <a href="{{ $img }}" target="_blank">
                        <img src="{{ $img }}" class="h-24 w-24 rounded-xl object-cover border border-slate-200 hover:opacity-80 transition" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ── Right Sidebar ───────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Meta --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $return->user->name ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $return->user->email ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Return Reason</p>
                    <p class="text-sm text-slate-700">{{ $return->returnReason->title ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Order</p>
                    @if($return->order)
                    <a href="{{ route('admin.orders.show', $return->order) }}" class="text-sm text-indigo-600 hover:underline font-medium">#{{ $return->order_serial_no }}</a>
                    @else
                    <p class="text-sm text-slate-700">#{{ $return->order_serial_no }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Refund Amount</p>
                    <p class="text-sm font-bold text-slate-900">{{ number_format($totalRefund, 2) }}</p>
                </div>
                @if($isRefunded)
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <p class="text-xs font-semibold text-emerald-700 mb-1">✓ Balance Credited</p>
                    <p class="text-xs text-emerald-600">{{ number_format($totalRefund, 2) }} credited to customer wallet on {{ $return->refund_issued_at?->format('M d, Y') }}.</p>
                </div>
                @endif
                @if($isRejected && $return->reject_reason)
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Rejection Reason</p>
                    <p class="text-sm text-slate-700">{{ $return->reject_reason }}</p>
                </div>
                @endif
            </div>

            {{-- ── Step 1: Accept / Reject (shown when pending or to reset) ── --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Step 1 — Review Request</p>
                <p class="text-xs text-slate-400 mb-4">
                    @if($isPending) Review the request below and accept or reject it.
                    @elseif($isAccepted) Request accepted. Proceed to refund steps →
                    @else Request rejected. You can reset to pending if needed.
                    @endif
                </p>

                @if(!$isAccepted)
                <form method="POST" action="{{ route('admin.returns.change-status', $return) }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="status" value="10">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Accept Return
                    </button>
                </form>
                @endif

                @if(!$isRejected)
                <form method="POST" action="{{ route('admin.returns.change-status', $return) }}" id="reject-form">
                    @csrf
                    <input type="hidden" name="status" value="15">
                    <textarea id="reject-reason-input" name="reason" rows="2"
                        placeholder="Rejection reason (required)…"
                        class="w-full mb-2 px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-300 resize-none"
                    >{{ old('reason', $return->reject_reason) }}</textarea>
                    <button type="button" onclick="submitReject()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reject Return
                    </button>
                </form>
                @endif

                @if(!$isPending)
                <form method="POST" action="{{ route('admin.returns.change-status', $return) }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="status" value="5">
                    <button type="submit" class="w-full px-4 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-200 transition-all">
                        ↺ Reset to Pending
                    </button>
                </form>
                @endif
            </div>

            {{-- ── Step 2: Item Received (shown once accepted) ─────────── --}}
            @if($isAccepted)
            <div class="bg-white rounded-3xl shadow-sm border {{ $isItemReceived || $isRefunded ? 'border-emerald-200' : 'border-amber-200' }} p-6">
                <p class="text-xs font-semibold {{ $isItemReceived || $isRefunded ? 'text-emerald-600' : 'text-amber-600' }} uppercase tracking-wider mb-1">Step 2 — Item Received</p>
                @if($isAwaiting)
                <p class="text-xs text-slate-400 mb-4">Waiting for the customer to ship the item back. Once it arrives at your warehouse, mark it as received.</p>
                <form method="POST" action="{{ route('admin.returns.process-refund', $return) }}">
                    @csrf
                    <input type="hidden" name="refund_status" value="{{ \App\Enums\RefundStatus::ITEM_RECEIVED }}">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                        Mark Item as Received
                    </button>
                </form>
                @else
                <p class="text-xs text-emerald-600 font-medium">✓ Item received and ready for inspection.</p>
                @endif
            </div>

            {{-- ── Step 3: Issue Refund (shown once item received) ──────── --}}
            <div class="bg-white rounded-3xl shadow-sm border {{ $isRefunded ? 'border-emerald-200' : ($isItemReceived ? 'border-indigo-200' : 'border-slate-200') }} p-6">
                <p class="text-xs font-semibold {{ $isRefunded ? 'text-emerald-600' : ($isItemReceived ? 'text-indigo-600' : 'text-slate-300') }} uppercase tracking-wider mb-1">Step 3 — Issue Refund</p>
                @if($isRefunded)
                <p class="text-xs text-emerald-600 font-medium">✓ Refund of {{ number_format($totalRefund, 2) }} issued on {{ $return->refund_issued_at?->format('M d, Y') }}.</p>
                @elseif($isItemReceived)
                <p class="text-xs text-slate-400 mb-4">Item inspected and confirmed. Issue the refund — this will credit <strong class="text-slate-700">{{ number_format($totalRefund, 2) }}</strong> to the customer's store balance.</p>
                <form method="POST" action="{{ route('admin.returns.process-refund', $return) }}" onsubmit="return confirm('Issue refund of {{ number_format($totalRefund, 2) }} to {{ $return->user->name ?? 'customer' }}?')">
                    @csrf
                    <input type="hidden" name="refund_status" value="{{ \App\Enums\RefundStatus::REFUND_ISSUED }}">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Issue Refund ({{ number_format($totalRefund, 2) }})
                    </button>
                </form>
                @else
                <p class="text-xs text-slate-300">Available after item is received.</p>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ── Return & Refund Audit Trail ────────────────────────────────── --}}
@php
    $returnAuditEvents = ['return_submitted','return_accepted','return_rejected','return_reset','refund_stage_5','refund_stage_10','refund_stage_15'];
    $returnAudits = $return->order
        ? $return->order->audits->filter(fn($a) => in_array($a->event, $returnAuditEvents))
        : collect();
@endphp
@if($returnAudits->isNotEmpty())
<div class="mt-6">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-1">Return & Refund Audit Trail</h3>
        <p class="text-xs text-slate-400 mb-5">History of all actions on this return request.</p>

        @php
        $eventCfg = [
            'return_submitted' => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
            'return_accepted'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'return_rejected'  => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'return_reset'     => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            'refund_stage_5'   => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            'refund_stage_10'  => ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700',  'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
            'refund_stage_15'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        $actorColors = ['admin' => 'text-indigo-600 bg-indigo-50', 'customer' => 'text-emerald-600 bg-emerald-50', 'system' => 'text-slate-500 bg-slate-100'];
        @endphp

        <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100" style="margin-left:1px;"></div>
            <div class="space-y-5">
                @foreach($returnAudits as $audit)
                @php
                    $cfg = $eventCfg[$audit->event] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                    $actorClass = $actorColors[$audit->actor_type] ?? 'text-slate-500 bg-slate-100';
                @endphp
                <div class="flex gap-4 relative">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full {{ $cfg['bg'] }} flex items-center justify-center z-10">
                        <svg class="w-4 h-4 {{ $cfg['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pb-1">
                        <p class="text-sm text-slate-800 leading-snug">{{ $audit->description }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-xs {{ $actorClass }} px-2 py-0.5 rounded-full font-medium">
                                {{ $audit->actor_name ?? ucfirst($audit->actor_type) }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $audit->created_at->format('M d, Y H:i') }}</span>
                            <span class="text-xs text-slate-300">{{ $audit->created_at->diffForHumans() }}</span>
                        </div>
                        @if(!empty($audit->meta['reason']))
                        <p class="text-xs text-slate-500 mt-1 italic">"{{ $audit->meta['reason'] }}"</p>
                        @endif
                        @if(!empty($audit->meta['amount']))
                        <p class="text-xs text-emerald-600 mt-1 font-semibold">Refund: {{ number_format($audit->meta['amount'], 2) }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<script>
function submitReject() {
    var reason = document.getElementById('reject-reason-input').value.trim();
    if (!reason) {
        alert('Please enter a rejection reason.');
        document.getElementById('reject-reason-input').focus();
        return;
    }
    document.getElementById('reject-form').submit();
}
</script>
@endsection
