@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')
@php
    $gateway   = \App\Models\PaymentGateway::find($order->payment_method);
    $gwSlug    = $gateway?->slug ?? '';
    $isCod     = in_array($gwSlug, ['cashondelivery', 'credit']);
    $isPaid    = $order->payment_status == 5;
    $gwLabels  = [
        'cashondelivery' => 'Cash on Delivery',
        'credit'         => 'Store Credit',
        'stripe'         => 'Stripe',
        'paypal'         => 'PayPal',
        'razorpay'       => 'Razorpay',
        'bkash'          => 'bKash',
        'mollie'         => 'Mollie',
        'flutterwave'    => 'Flutterwave',
    ];
    $gwName = $gwLabels[$gwSlug] ?? ucfirst($gwSlug ?: 'Unknown');

    $payload = $order->reasonPayload();

    $orderStatusText = [1=>'Pending',5=>'Confirmed',7=>'On the Way',10=>'Delivered',15=>'Canceled',20=>'Rejected'];
    $orderStatusClass = [
        1  => 'bg-amber-100 text-amber-800',
        5  => 'bg-blue-100 text-blue-800',
        7  => 'bg-indigo-100 text-indigo-800',
        10 => 'bg-emerald-100 text-emerald-800',
        15 => 'bg-rose-100 text-rose-800',
        20 => 'bg-slate-100 text-slate-600',
    ];
    $transaction = $order->transaction;
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="admin-page-title">Order #{{ $order->order_serial_no }}</h1>
                @if(isset($payload['cancellation_requested']) && $payload['cancellation_requested'])
                    <span class="px-3 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full tracking-wider uppercase">Cancellation Requested</span>
                @endif
            </div>
            <p class="admin-page-subtitle">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button"
                onclick="confirmSubmit('del-order-detail', { title: 'Archive Order', message: 'This order will be archived and hidden from the main list. You can restore it later from Archived Orders.', confirmText: 'Yes, Archive', type: 'danger' })"
                class="px-5 py-2.5 bg-white text-rose-600 border border-rose-200 text-sm font-semibold rounded-xl hover:bg-rose-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12"/>
                </svg>
                Archive Order
            </button>
            <form id="del-order-detail" action="{{ route('admin.orders.destroy', $order) }}" method="POST" style="display:none;">
                @csrf @method('DELETE')
            </form>
            <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                ← Back to Orders
            </a>
        </div>
    </div>

    @include('admin._alerts')

    {{-- ── At-a-glance status bar ──────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        {{-- Order Status --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Order Status</p>
            <span class="px-3 py-1.5 text-sm font-bold rounded-xl {{ $orderStatusClass[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                {{ $orderStatusText[$order->status] ?? 'Unknown' }}
            </span>
        </div>

        {{-- Payment Status --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Payment Status</p>
            @if($isPaid)
                <span class="px-3 py-1.5 text-sm font-bold rounded-xl bg-emerald-100 text-emerald-800">Paid</span>
            @elseif($isCod)
                <span class="px-3 py-1.5 text-sm font-bold rounded-xl bg-amber-100 text-amber-800">Pay on Delivery</span>
                <p class="text-xs text-slate-400 mt-1">Collect payment upon delivery</p>
            @else
                <span class="px-3 py-1.5 text-sm font-bold rounded-xl bg-rose-100 text-rose-800">Awaiting Payment</span>
            @endif
        </div>

        {{-- Payment Method --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Payment Method</p>
            <p class="text-sm font-bold text-slate-900">{{ $gwName }}</p>
            @if($transaction)
                <p class="text-xs text-slate-400 mt-0.5 font-mono truncate" title="{{ $transaction->transaction_no }}">
                    Txn: {{ Str::limit($transaction->transaction_no, 20) }}
                </p>
            @endif
        </div>

        {{-- Order Type --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Fulfilment</p>
            <p class="text-sm font-bold text-slate-900">
                @if($order->order_type == 10) Pick Up
                @elseif($order->order_type == 15) POS
                @else Delivery
                @endif
            </p>
            <p class="text-xs text-slate-400 mt-0.5">
                via {{ $order->source == 10 ? 'App' : ($order->source == 15 ? 'POS Terminal' : 'Website') }}
            </p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="admin-card-grid">
        <div class="admin-card-grid-main">
            <!-- Order Items Section -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Order Items</h2>
                    <p class="admin-card-subtitle">{{ $order->orderProducts->count() }} item(s)</p>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($order->orderProducts as $item)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100 hover:border-slate-200 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-16 h-16 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                @if($item->product && $item->product->getFirstMediaUrl('product'))
                                    <img src="{{ $item->product->getFirstMediaUrl('product', 'thumb') }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $item->product?->name ?? 'N/A' }}</p>
                                @if($item->variation_names)
                                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-tight">{{ $item->variation_names }}</p>
                                @endif
                                <p class="text-sm text-slate-600">{{ $currencySymbol }}{{ number_format($item->price, 2) }} × {{ abs($item->quantity) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg text-indigo-600">{{ $currencySymbol }}{{ number_format($item->price * abs($item->quantity), 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="mt-6 pt-6 border-t border-slate-200 space-y-3">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-900">{{ $currencySymbol }}{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Shipping</span>
                        <span class="font-medium text-slate-900">{{ $currencySymbol }}{{ number_format($order->shipping_charge, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Discount</span>
                        <span class="font-medium text-rose-600">-{{ $currencySymbol }}{{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($order->tax > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Tax</span>
                        <span class="font-medium text-slate-900">{{ $currencySymbol }}{{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                        <span class="text-slate-900">Total</span>
                        <span class="text-indigo-600">{{ $currencySymbol }}{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Customer</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold flex-shrink-0">
                                {{ substr($order->user->name ?? 'G', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $order->user->name ?? 'Guest' }}</p>
                                <p class="text-sm text-slate-500">{{ $order->user->email ?? 'N/A' }}</p>
                                <p class="text-sm text-slate-500">{{ $order->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">{{ $order->order_type == 10 ? 'Pickup Address' : 'Shipping Address' }}</h2>
                    </div>
                    @php
                        $deliveryAddress = $order->address->first();
                        $pickupAddress   = $order->outletAddress;
                        $address         = $deliveryAddress ?: $pickupAddress;
                    @endphp
                    @if($address)
                        <div class="space-y-2 text-sm text-slate-600">
                            <p class="font-semibold text-slate-900">{{ $address->full_name ?? $address->name ?? 'N/A' }}</p>
                            @if(!empty($address->phone))
                                <p>{{ trim(($address->country_code ?? '') . ' ' . $address->phone) }}</p>
                            @endif
                            <p>{{ $address->address ?? 'N/A' }}</p>
                            <p>{{ $address->city ?? 'N/A' }}, {{ $address->state ?? 'N/A' }} {{ $address->zip_code ?? '' }}</p>
                            @if(!empty($address->country))<p>{{ $address->country }}</p>@endif
                        </div>
                    @else
                        <p class="text-slate-400 italic text-sm">No address information.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card-grid-side">

            {{-- ── Cancellation Request alert ──────────────────────────────── --}}
            @if(isset($payload['cancellation_requested']) && $payload['cancellation_requested'])
            <div class="admin-card bg-rose-50 border-rose-200">
                <div class="p-1">
                    <div class="flex items-center gap-2 text-rose-800 font-bold mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Cancellation Requested
                    </div>
                    <p class="text-sm text-rose-600 mb-4 leading-relaxed">
                        The customer wants to cancel this order. Accept below to cancel and notify them.
                    </p>
                    <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="15">
                        <input type="hidden" name="reason" value="Cancellation request accepted by admin.">
                        <input type="hidden" name="send_email" value="1">
                        <button type="submit" class="w-full py-2.5 bg-rose-600 text-white text-sm font-bold rounded-xl hover:bg-rose-700 transition">
                            Accept & Cancel Order
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- ── Update Order Status ─────────────────────────────────────── --}}
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Update Order Status</h2>
                </div>
                <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="admin-form-field">
                        <label for="status" class="admin-form-label">Order Status</label>
                        <select name="status" id="status" class="admin-form-select">
                            <option value="1"  {{ $order->status == 1  ? 'selected':'' }}>Pending</option>
                            <option value="5"  {{ $order->status == 5  ? 'selected':'' }}>Confirmed</option>
                            <option value="7"  {{ $order->status == 7  ? 'selected':'' }}>On the Way</option>
                            <option value="10" {{ $order->status == 10 ? 'selected':'' }}>Delivered</option>
                            <option value="15" {{ $order->status == 15 ? 'selected':'' }}>Canceled</option>
                            <option value="20" {{ $order->status == 20 ? 'selected':'' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="admin-form-field">
                        <label for="reason" class="admin-form-label">
                            Reason <span class="text-slate-400 font-normal">(required for Cancel / Reject)</span>
                        </label>
                        <textarea name="reason" id="reason" rows="2" class="admin-form-input"
                            placeholder="e.g. Out of stock, customer request…">{{ old('reason', $order->adminStatusReason()) }}</textarea>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" name="send_email" id="send_email_status" value="1" checked class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="send_email_status" class="text-sm font-medium text-slate-700">Notify customer by email</label>
                    </div>
                    <button type="submit" class="admin-btn-primary w-full">Update Status</button>
                </form>
            </div>

            {{-- ── Mark Payment Status ─────────────────────────────────────── --}}
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Mark Payment</h2>
                    <p class="admin-card-subtitle">
                        @if($isCod) Use this to record when cash is collected on delivery.
                        @else Override the payment status if needed.
                        @endif
                    </p>
                </div>
                <form action="{{ route('admin.orders.payment-status.update', $order) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="admin-form-field">
                        <label for="payment_status" class="admin-form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="admin-form-select">
                            <option value="5"  {{ $order->payment_status == 5  ? 'selected':'' }}>Paid</option>
                            <option value="10" {{ $order->payment_status == 10 ? 'selected':'' }}>
                                {{ $isCod ? 'Not Yet Collected' : 'Unpaid' }}
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="admin-btn-secondary w-full">
                        {{ $isCod ? 'Record Payment Collection' : 'Update Payment Status' }}
                    </button>
                </form>

                {{-- Transaction record --}}
                @if($transaction)
                @php
                    $refundTransaction = \App\Models\Transaction::where(['order_id' => $order->id, 'type' => 'cash_back'])->first();
                    $isStripePayment   = $transaction->payment_method === 'stripe';
                    $isCancelledOrder  = in_array($order->status, [15, 20]); // CANCELED or REJECTED
                @endphp
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaction Record</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Amount</span>
                        <span class="font-bold text-slate-900">{{ $currencySymbol }}{{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Gateway</span>
                        <span class="font-semibold text-slate-900">{{ ucfirst($transaction->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Type</span>
                        <span class="font-semibold text-slate-900 capitalize">{{ str_replace('_', ' ', $transaction->type) }}</span>
                    </div>
                    <div class="text-xs text-slate-400 break-all font-mono mt-1">{{ $transaction->transaction_no }}</div>
                </div>

                {{-- ── Refund Section (only for paid + cancelled/rejected orders) ── --}}
                @if($isPaid && $isCancelledOrder)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    @if($refundTransaction)
                        {{-- Refund already issued --}}
                        <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-700">Refund issued</p>
                                <p class="text-xs text-emerald-600">
                                    {{ $currencySymbol }}{{ number_format($refundTransaction->amount, 2) }}
                                    @if($isStripePayment) returned to card @else credited to wallet @endif
                                    · Ref: <span class="font-mono">{{ $refundTransaction->transaction_no }}</span>
                                </p>
                            </div>
                        </div>
                    @else
                        {{-- Refund pending admin action --}}
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                            <p class="text-sm font-semibold text-amber-700 mb-1">Refund not yet issued</p>
                            <p class="text-xs text-amber-600">
                                This order was paid via <strong>{{ ucfirst($transaction->payment_method) }}</strong>.
                                Amount to refund: <strong>{{ $currencySymbol }}{{ number_format($order->total, 2) }}</strong>.
                                @if($isStripePayment)
                                    Clicking below will refund the full amount directly to the customer's card via Stripe.
                                @else
                                    Clicking below will credit the customer's store wallet.
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.orders.issue-refund', $order) }}"
                              onsubmit="return confirm('Issue refund of {{ $currencySymbol }}{{ number_format($order->total, 2) }} to this customer? This cannot be undone.')">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Issue Refund — {{ $currencySymbol }}{{ number_format($order->total, 2) }}
                                @if($isStripePayment) (Stripe → Card) @else (Wallet Credit) @endif
                            </button>
                        </form>
                    @endif
                </div>
                @endif
                @endif
            </div>

            {{-- ── Notes ──────────────────────────────────────────────────── --}}
            @if($order->customerNote() || $order->adminStatusReason())
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Notes</h2>
                </div>
                <div class="space-y-4">
                    @if($order->customerNote())
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer Note</p>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $order->customerNote() }}</p>
                    </div>
                    @endif
                    @if($order->adminStatusReason())
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status Reason</p>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $order->adminStatusReason() }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Customer Messages ───────────────────────────────────────── --}}
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Customer Messages</h2>
                    <p class="admin-card-subtitle">{{ $order->messages()->count() }} message(s)</p>
                </div>
                <div class="space-y-3 mb-5" id="admin-messages-list" style="max-height:400px;overflow-y:auto;">
                    @forelse($order->messages()->with('user:id,name')->orderBy('created_at')->get() as $msg)
                        @php $isCancelRequest = str_starts_with($msg->message, '[CANCELLATION REQUEST]'); @endphp
                        <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-2xl px-4 py-3
                                {{ $isCancelRequest ? 'bg-rose-50 border border-rose-200 text-rose-800' : ($msg->sender_type === 'admin' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800') }}">
                                <p class="text-xs font-semibold mb-1 {{ $isCancelRequest ? 'text-rose-500' : ($msg->sender_type === 'admin' ? 'text-indigo-200' : 'text-slate-500') }}">
                                    @if($isCancelRequest) ⚠ Cancellation Request — {{ $msg->user->name ?? 'Customer' }}
                                    @elseif($msg->sender_type === 'admin') Support Team (You)
                                    @else {{ $msg->user->name ?? 'Customer' }}
                                    @endif
                                </p>
                                <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $isCancelRequest ? ltrim(substr($msg->message, strlen('[CANCELLATION REQUEST]'))) : $msg->message }}</p>
                                <p class="text-xs mt-1 {{ $isCancelRequest ? 'text-rose-400' : ($msg->sender_type === 'admin' ? 'text-indigo-300' : 'text-slate-400') }}">{{ $msg->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-6">No messages yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('admin.orders.reply', $order) }}" class="space-y-3">
                    @csrf
                    <div class="flex gap-3">
                        <textarea name="message" rows="2" required maxlength="2000"
                            placeholder="Type a reply to the customer…"
                            class="flex-1 px-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition self-end">Send</button>
                    </div>
                    <div class="flex items-center gap-2 px-1">
                        <input type="checkbox" name="send_email" id="send_email_reply" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="send_email_reply" class="text-sm font-medium text-slate-600">Send copy to customer email</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Audit Trail ──────────────────────────────────────────────── --}}
    @php $audits = $order->audits; @endphp
    @if($audits->isNotEmpty())
    <div class="mt-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Order History</h3>
            <p class="text-xs text-slate-400 mb-5">All actions taken on this order, oldest to newest.</p>
            @php
            $eventConfig = [
                'order_placed'           => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'payment_confirmed'      => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                'status_changed'         => ['bg'=>'bg-indigo-100','text'=>'text-indigo-700','icon'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                'payment_status_changed' => ['bg'=>'bg-blue-100','text'=>'text-blue-700','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                'cancellation_requested' => ['bg'=>'bg-rose-100','text'=>'text-rose-700','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'return_submitted'       => ['bg'=>'bg-amber-100','text'=>'text-amber-700','icon'=>'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                'return_accepted'        => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'return_rejected'        => ['bg'=>'bg-rose-100','text'=>'text-rose-700','icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            $actorColors = ['admin'=>'text-indigo-600 bg-indigo-50','customer'=>'text-emerald-600 bg-emerald-50','system'=>'text-slate-500 bg-slate-100'];
            @endphp
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100" style="margin-left:1px;"></div>
                <div class="space-y-5">
                    @foreach($audits as $audit)
                    @php
                        $cfg = $eventConfig[$audit->event] ?? ['bg'=>'bg-slate-100','text'=>'text-slate-600','icon'=>'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
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
                            @if($audit->meta && isset($audit->meta['reason']) && $audit->meta['reason'])
                            <p class="text-xs text-slate-500 mt-1 italic">"{{ $audit->meta['reason'] }}"</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
