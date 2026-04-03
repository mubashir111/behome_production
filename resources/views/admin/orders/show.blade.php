@extends('layouts.admin')

@section('title', 'Order Detail')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="admin-page-title">Order #{{ $order->order_serial_no }}</h1>
                @php $payload = $order->reasonPayload(); @endphp
                @if(isset($payload['cancellation_requested']) && $payload['cancellation_requested'])
                    <span class="px-3 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full tracking-wider uppercase">Cancellation Requested</span>
                @endif
            </div>
            <p class="admin-page-subtitle">Placed on {{ $order->created_at->format('M d, Y \a\t H:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                onclick="confirmSubmit('del-order-detail', { title: 'Delete Order', message: 'Are you sure you want to delete this order? This action is permanent and will remove all associated records.', confirmText: 'Yes, Delete', type: 'danger' })" 
                class="px-5 py-2.5 bg-white text-rose-600 border border-rose-200 text-sm font-semibold rounded-xl hover:bg-rose-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete Order
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

    <!-- Main Content Grid -->
    <div class="admin-card-grid">
        <div class="admin-card-grid-main">
            <!-- Order Items Section -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Order Items</h2>
                    <p class="admin-card-subtitle">{{ $order->orderProducts->count() }} item(s) in this order</p>
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
                                <p class="font-semibold text-slate-900">{{ $item->product_name }}</p>
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
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                        <span class="text-slate-900">Total</span>
                        <span class="text-indigo-600">{{ $currencySymbol }}{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Customer Information</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold flex-shrink-0">
                                {{ substr($order->user->name ?? 'G', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-500 font-medium">Name</p>
                                <p class="font-semibold text-slate-900">{{ $order->user->name ?? 'Guest' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Email</p>
                            <p class="font-semibold text-slate-900">{{ $order->user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-medium">Phone</p>
                            <p class="font-semibold text-slate-900">{{ $order->user->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">{{ $order->order_type == 1 ? 'Shipping Address' : 'Pickup Address' }}</h2>
                    </div>
                    @php
                        $deliveryAddress = $order->address->first();
                        $pickupAddress = $order->outletAddress;
                        $address = $deliveryAddress ?: $pickupAddress;
                    @endphp
                    @if($address)
                        <div class="space-y-3 text-sm text-slate-600">
                            <div>
                                <p class="text-sm text-slate-500 font-medium">Name</p>
                                <p class="font-semibold text-slate-900">{{ $address->full_name ?? $address->name ?? 'N/A' }}</p>
                            </div>
                            @if(!empty($address->email))
                                <div>
                                    <p class="text-sm text-slate-500 font-medium">Email</p>
                                    <p class="font-semibold text-slate-900">{{ $address->email }}</p>
                                </div>
                            @endif
                            @if(!empty($address->phone))
                                <div>
                                    <p class="text-sm text-slate-500 font-medium">Phone</p>
                                    <p class="font-semibold text-slate-900">{{ trim(($address->country_code ?? '') . ' ' . $address->phone) }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-sm text-slate-500 font-medium">Address</p>
                                <p class="font-semibold text-slate-900">{{ $address->address ?? 'N/A' }}</p>
                                <p>{{ $address->city ?? 'N/A' }}, {{ $address->state ?? 'N/A' }} {{ $address->zip_code ?? 'N/A' }}</p>
                                @if(!empty($address->country))
                                    <p>{{ $address->country }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-slate-500 italic">No address information available.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card-grid-side">
            <!-- Order Status Card -->
            @if(isset($payload['cancellation_requested']) && $payload['cancellation_requested'])
                <div class="admin-card bg-rose-50 border-rose-200">
                    <div class="p-1">
                        <div class="flex items-center gap-2 text-rose-800 font-bold mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Cancellation Requested
                        </div>
                        <p class="text-sm text-rose-600 mb-4 leading-relaxed">
                            The customer has requested to cancel this order. You can accept the request below to automatically cancel the order and notify the customer.
                        </p>
                        
                        <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="15"> {{-- 15 = Canceled --}}
                            <input type="hidden" name="reason" value="Cancellation request accepted by admin.">
                            <input type="hidden" name="send_email" value="1">
                            
                            <button type="submit" class="w-full py-2.5 bg-rose-600 text-white text-sm font-bold rounded-xl hover:bg-rose-700 transition shadow-sm shadow-rose-200">
                                Accept Cancellation Request
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Manage Order Status</h2>
                </div>
                
                <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="admin-form-field">
                        <label for="status" class="admin-form-label">Order Status</label>
                        <select name="status" id="status" class="admin-form-select">
                            <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Pending</option>
                            <option value="5" {{ $order->status == 5 ? 'selected' : '' }}>Confirmed</option>
                            <option value="7" {{ $order->status == 7 ? 'selected' : '' }}>On the Way</option>
                            <option value="10" {{ $order->status == 10 ? 'selected' : '' }}>Delivered</option>
                            <option value="15" {{ $order->status == 15 ? 'selected' : '' }}>Canceled</option>
                            <option value="20" {{ $order->status == 20 ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="admin-form-field">
                        <label for="reason" class="admin-form-label">Reason</label>
                        <textarea name="reason" id="reason" rows="2" class="admin-form-input" placeholder="Required when canceling or rejecting an order.">{{ old('reason', $order->adminStatusReason()) }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" name="send_email" id="send_email_status" value="1" checked class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="send_email_status" class="text-sm font-medium text-slate-700">Send Email Notification</label>
                    </div>

                    <button type="submit" class="admin-btn-primary w-full">
                        Update Status
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-200 space-y-3">
                    <form action="{{ route('admin.orders.payment-status.update', $order) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="admin-form-field">
                            <label for="payment_status" class="admin-form-label">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="admin-form-select">
                                <option value="5" {{ $order->payment_status == 5 ? 'selected' : '' }}>Paid</option>
                                <option value="10" {{ $order->payment_status == 10 ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <button type="submit" class="admin-btn-secondary w-full">Update Payment Status</button>
                    </form>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600">Order Type</span>
                        <span class="font-semibold text-slate-900">{{ $order->order_type == 1 ? 'Home Delivery' : 'POS' }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Notes & Details</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium mb-1">Customer Note</p>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ $order->customerNote() ?: 'No customer note provided.' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium mb-1">Status Reason</p>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ $order->adminStatusReason() ?: 'No cancel/reject reason provided.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Messages -->
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
                                    @if($isCancelRequest)
                                        ⚠ Cancellation Request — {{ $msg->user->name ?? 'Customer' }}
                                    @elseif($msg->sender_type === 'admin')
                                        Support Team (You)
                                    @else
                                        {{ $msg->user->name ?? 'Customer' }}
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

                <!-- Reply Form -->
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
                        <label for="send_email_reply" class="text-sm font-medium text-slate-600">Send Copy to Customer Email</label>
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
            <h3 class="text-sm font-bold text-slate-900 mb-1">Audit Trail</h3>
            <p class="text-xs text-slate-400 mb-5">Complete history of all actions taken on this order.</p>

            @php
            $eventConfig = [
                'order_placed'           => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'payment_confirmed'      => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                'status_changed'         => ['bg' => 'bg-indigo-100',   'text' => 'text-indigo-700',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                'payment_status_changed' => ['bg' => 'bg-blue-100',     'text' => 'text-blue-700',    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                'cancellation_requested' => ['bg' => 'bg-rose-100',     'text' => 'text-rose-700',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'return_submitted'       => ['bg' => 'bg-amber-100',    'text' => 'text-amber-700',   'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                'return_accepted'        => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'return_rejected'        => ['bg' => 'bg-rose-100',     'text' => 'text-rose-700',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'return_reset'           => ['bg' => 'bg-slate-100',    'text' => 'text-slate-600',   'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                'refund_stage_10'        => ['bg' => 'bg-indigo-100',   'text' => 'text-indigo-700',  'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                'refund_stage_15'        => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'refund_stage_5'         => ['bg' => 'bg-amber-100',    'text' => 'text-amber-700',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            $actorColors = ['admin' => 'text-indigo-600 bg-indigo-50', 'customer' => 'text-emerald-600 bg-emerald-50', 'system' => 'text-slate-500 bg-slate-100'];
            @endphp

            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100" style="margin-left:1px;"></div>

                <div class="space-y-5">
                    @foreach($audits as $audit)
                    @php
                        $cfg = $eventConfig[$audit->event] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                        $actorClass = $actorColors[$audit->actor_type] ?? 'text-slate-500 bg-slate-100';
                    @endphp
                    <div class="flex gap-4 relative">
                        {{-- Icon dot --}}
                        <div class="flex-shrink-0 w-9 h-9 rounded-full {{ $cfg['bg'] }} flex items-center justify-center z-10">
                            <svg class="w-4 h-4 {{ $cfg['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                            </svg>
                        </div>

                        {{-- Content --}}
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
