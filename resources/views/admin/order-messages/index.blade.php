@extends('layouts.admin')
@section('title', 'Order Messages')
@section('content')

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Order Messages</h2>
            <p class="admin-page-subtitle">Customer messages, cancellation requests and support threads.</p>
        </div>
    </div>

    @include('admin._alerts')

    {{-- Filter Tabs --}}
    <div class="flex gap-3 mb-6 flex-wrap">
        <a href="{{ route('admin.order-messages.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            All Threads
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $filter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $orders->total() }}</span>
        </a>
        <a href="{{ route('admin.order-messages.index', ['filter' => 'cancellation']) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'cancellation' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            ⚠ Cancellation Requests
            @if($cancellationCount)
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $filter === 'cancellation' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-600' }}">{{ $cancellationCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.order-messages.index', ['filter' => 'unread']) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $filter === 'unread' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Unread
            @if($unreadCount)
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ $filter === 'unread' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    {{-- Threads --}}
    @forelse($orders as $order)
        @php
            $hasCancelRequest = $order->messages->contains(fn($m) => str_starts_with($m->message, '[CANCELLATION REQUEST]'));
            $unreadMsgs = $order->messages->where('sender_type', 'customer')->where('is_read', false)->count();
        @endphp

        <div class="bg-white rounded-2xl border {{ $hasCancelRequest ? 'border-rose-200' : 'border-slate-200' }} shadow-sm mb-5 overflow-hidden">

            {{-- Thread Header --}}
            <div class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap {{ $hasCancelRequest ? 'bg-rose-50' : 'bg-slate-50/60' }} border-b {{ $hasCancelRequest ? 'border-rose-100' : 'border-slate-100' }}">
                <div class="flex items-center gap-3">
                    @if($hasCancelRequest)
                        <span class="px-2.5 py-1 text-xs font-bold bg-rose-100 text-rose-700 rounded-lg">⚠ Cancellation Request</span>
                    @endif
                    @if($unreadMsgs)
                        <span class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-700 rounded-lg">{{ $unreadMsgs }} unread</span>
                    @endif
                    <div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600">
                            Order #{{ $order->order_serial_no }}
                        </a>
                        <span class="text-slate-400 text-xs ml-2">{{ $order->user->name ?? 'Guest' }} &bull; {{ $order->user->email ?? '' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [1=>'amber',5=>'blue',7=>'indigo',10=>'emerald',15=>'rose',20=>'slate'];
                        $statusText   = [1=>'Pending',5=>'Confirmed',7=>'On the Way',10=>'Delivered',15=>'Cancelled',20=>'Rejected'];
                        $sc = $statusColors[$order->status] ?? 'slate';
                    @endphp
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg text-{{ $sc }}-700 bg-{{ $sc }}-50">{{ $statusText[$order->status] ?? 'Unknown' }}</span>
                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs text-indigo-600 hover:underline font-semibold">View Order →</a>
                </div>
            </div>

            {{-- Message Thread --}}
            <div class="px-6 py-4 space-y-3" data-thread style="max-height:320px;overflow-y:auto;">
                @foreach($order->messages->sortBy('created_at') as $msg)
                    @php $isCancel = str_starts_with($msg->message, '[CANCELLATION REQUEST]'); @endphp
                    <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-3
                            {{ $isCancel ? 'bg-rose-50 border border-rose-200' : ($msg->sender_type === 'admin' ? 'bg-indigo-600' : 'bg-slate-100') }}">
                            <p class="text-xs font-semibold mb-1
                                {{ $isCancel ? 'text-rose-600' : ($msg->sender_type === 'admin' ? 'text-indigo-200' : 'text-slate-500') }}">
                                @if($isCancel) ⚠ Cancellation Request
                                @elseif($msg->sender_type === 'admin') Support Team
                                @else {{ $msg->user->name ?? 'Customer' }}
                                @endif
                                @if($msg->sender_type === 'customer' && !$msg->is_read)
                                    <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px]">New</span>
                                @endif
                            </p>
                            <p class="text-sm leading-relaxed whitespace-pre-wrap {{ $isCancel ? 'text-rose-800' : ($msg->sender_type === 'admin' ? 'text-white' : 'text-slate-700') }}">{{ $isCancel ? ltrim(substr($msg->message, strlen('[CANCELLATION REQUEST]'))) : $msg->message }}</p>
                            <p class="text-xs mt-1 {{ $isCancel ? 'text-rose-400' : ($msg->sender_type === 'admin' ? 'text-indigo-300' : 'text-slate-400') }}">{{ $msg->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Reply Form --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-white">
                <form method="POST" action="{{ route('admin.order-messages.reply', $order) }}" class="flex gap-3">
                    @csrf
                    <input type="text" name="message" required maxlength="2000"
                        placeholder="Type a reply to {{ $order->user->name ?? 'customer' }}…"
                        class="flex-1 px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-slate-50" />
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition whitespace-nowrap">
                        Send Reply
                    </button>
                    @if($hasCancelRequest && $order->status === 5)
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700 transition whitespace-nowrap">
                            Cancel Order
                        </a>
                    @endif
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
            <svg class="w-12 h-12 text-slate-200 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-slate-500 text-sm">No messages found.</p>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    // Show a "new messages" banner and auto-reload every 30 seconds
    let pollInterval = 30000;
    let newMsgBanner = null;

    function createBanner() {
        if (newMsgBanner) return;
        newMsgBanner = document.createElement('div');
        newMsgBanner.id = 'new-msg-banner';
        newMsgBanner.innerHTML = '🔔 New messages arrived — <strong>click to refresh</strong>';
        Object.assign(newMsgBanner.style, {
            position: 'fixed', bottom: '24px', right: '24px', zIndex: '9999',
            background: '#4f46e5', color: '#fff', padding: '12px 20px',
            borderRadius: '12px', fontWeight: '600', fontSize: '13px',
            boxShadow: '0 8px 24px rgba(79,70,229,0.35)', cursor: 'pointer',
            transition: 'opacity 0.2s',
        });
        newMsgBanner.onclick = () => location.reload();
        document.body.appendChild(newMsgBanner);
    }

    function poll() {
        fetch('{{ route('admin.order-messages.index') }}?_poll=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .catch(() => null)
        .then(data => {
            if (!data) return;
            // If unread count increased vs what page loaded with, prompt refresh
            if (data.unreadCount > {{ $unreadCount }}) {
                createBanner();
            }
        });
    }

    setInterval(poll, pollInterval);

    // Scroll each message thread to the bottom on load
    document.querySelectorAll('[data-thread]').forEach(el => {
        el.scrollTop = el.scrollHeight;
    });
})();
</script>
@endpush
@endsection
