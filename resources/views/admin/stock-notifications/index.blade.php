@extends('layouts.admin')

@section('title', 'Stock Notifications')

@section('content')
<div class="max-w-7xl mx-auto pb-12">

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Stock Notifications</h2>
        <p class="text-slate-500 mt-1">Customers waiting for out-of-stock products. Send them an email when the item is back.</p>
    </div>

    @include('admin._alerts')

    {{-- Summary cards --}}
    @if($summary->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($summary as $item)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400 mb-0.5">{{ $item->product->name ?? 'Unknown' }}</p>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-slate-800">{{ $item->total }}</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $item->pending > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $item->pending }} pending
                    </span>
                </div>
            </div>

            @if($item->pending > 0)
            {{-- Quick send trigger --}}
            <button type="button"
                onclick="openSendModal({{ $item->product_id }}, '{{ addslashes($item->product->name ?? '') }}')"
                class="ml-auto flex-shrink-0 px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition-all">
                Send
            </button>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.stock-notifications.index') }}" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="product_id" placeholder="Product ID" value="{{ request('product_id') }}"
            class="border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select name="status" class="border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="notified" {{ request('status') === 'notified' ? 'selected' : '' }}>Notified</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-slate-700 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-all">Filter</button>
        @if(request('product_id') || request('status'))
            <a href="{{ route('admin.stock-notifications.index') }}" class="px-5 py-2 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Product</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Subscribed At</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $notif)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                @if($notif->product?->cover)
                                <img src="{{ $notif->product->cover }}" alt="{{ $notif->product->name }}"
                                    class="w-10 h-10 rounded-xl object-cover border border-slate-100">
                                @else
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">{{ $notif->product->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">ID: {{ $notif->product_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-700">{{ $notif->email }}</td>
                        <td class="px-6 py-5">
                            @if($notif->notified)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Notified
                                </span>
                                @if($notif->notified_at)
                                <p class="text-xs text-slate-400 mt-1">{{ $notif->notified_at->format('M d, Y') }}</p>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-500">{{ $notif->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center gap-2">
                                @if(!$notif->notified)
                                <button type="button"
                                    onclick="openSendModal({{ $notif->product_id }}, '{{ addslashes($notif->product->name ?? '') }}')"
                                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all"
                                    title="Send notification">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                                @endif
                                <button type="button"
                                    onclick="confirmSubmit('del-notif-{{ $notif->id }}', { title: 'Remove Subscriber', message: 'Remove this subscriber?', confirmText: 'Remove', type: 'danger' })"
                                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <form id="del-notif-{{ $notif->id }}" action="{{ route('admin.stock-notifications.destroy', $notif->id) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                            No stock notification subscribers yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
        <div class="px-8 py-6 bg-slate-50 border-t border-slate-200">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Send Notification Modal --}}
<div id="send-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">Send Stock Notification</h3>
                <p id="modal-product-name" class="text-sm text-slate-500"></p>
            </div>
        </div>

        <form id="send-form" method="POST" action="{{ route('admin.stock-notifications.send') }}">
            @csrf
            <input type="hidden" name="product_id" id="modal-product-id">

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Custom Message <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="custom_message" rows="4" placeholder="Leave blank to use the default message: 'The item you were waiting for is now available…'"
                    class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>

            <p class="text-xs text-slate-400 mb-6">This will email <strong>all pending subscribers</strong> for this product and mark them as notified.</p>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all">
                    Send Emails
                </button>
                <button type="button" onclick="closeSendModal()" class="flex-1 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openSendModal(productId, productName) {
    document.getElementById('modal-product-id').value   = productId;
    document.getElementById('modal-product-name').textContent = productName;
    const modal = document.getElementById('send-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeSendModal() {
    const modal = document.getElementById('send-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('send-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSendModal();
});
</script>
@endpush
