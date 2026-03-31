@extends('layouts.admin')

@section('title', 'Purchase PO-' . str_pad($purchase->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-[1000px] mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Purchase Order #PO-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}</h2>
            <p class="text-slate-500 mt-1">Logged on {{ \Carbon\Carbon::parse($purchase->date)->format('F d, Y') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.purchases.index') }}" class="px-6 py-3 bg-white text-slate-600 font-bold rounded-2xl hover:bg-slate-50 border border-slate-300 transition-all shadow-sm">
                ← Back
            </a>
            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                Edit Purchase
            </a>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] border border-slate-300 shadow-sm overflow-hidden">
        <!-- Status Top Banner -->
        @if($purchase->status == 15)
            <div class="bg-emerald-50 border-b border-emerald-100 px-8 py-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-emerald-800">Received</h4>
                    <p class="text-xs text-emerald-600">Stock from this order has been added to live inventory.</p>
                </div>
            </div>
        @elseif($purchase->status == 10)
             <div class="bg-amber-50 border-b border-amber-100 px-8 py-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-amber-800">Ordered</h4>
                    <p class="text-xs text-amber-600">Waiting for delivery. Stock is not active yet.</p>
                </div>
            </div>
        @else
            <div class="bg-slate-50 border-b border-slate-200 px-8 py-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Pending</h4>
                    <p class="text-xs text-slate-500">Draft purchase request.</p>
                </div>
            </div>
        @endif

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 border-b border-slate-100 pb-8 mb-8">
                <!-- Supplier Info -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Supplier Information</h4>
                    @if($purchase->supplier)
                        <div class="font-bold text-slate-900 text-lg">{{ $purchase->supplier->name }}</div>
                        <div class="text-slate-500 mt-1">{{ $purchase->supplier->company }}</div>
                        <div class="mt-3 text-sm text-slate-600">
                            <strong>Email:</strong> {{ $purchase->supplier->email ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $purchase->supplier->phone ?? 'N/A' }}
                        </div>
                    @else
                        <div class="text-slate-500 italic">Unknown Supplier</div>
                    @endif
                </div>

                <!-- PO Info -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Purchase Details</h4>
                    <table class="w-full text-sm">
                        <tbody>
                            <tr><td class="py-2 text-slate-500 w-1/3">Reference No</td><td class="py-2 font-medium text-slate-900">{{ $purchase->reference_no ?: 'N/A' }}</td></tr>
                            <tr><td class="py-2 text-slate-500">Total Amount</td><td class="py-2 font-bold text-indigo-600">{{ env('CURRENCY_SYMBOL', '₹') }}{{ number_format($purchase->total, 2) }}</td></tr>
                            <tr>
                                <td class="py-2 text-slate-500">Attachment</td>
                                <td class="py-2">
                                    @if($purchase->getFirstMediaUrl('purchase'))
                                        <a href="{{ route('admin.purchases.downloadAttachment', $purchase->id) }}" class="text-indigo-600 font-bold hover:underline inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Download File</a>
                                    @else
                                        <span class="text-slate-400 italic">None</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Items table -->
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Purchased Items</h4>
            <div class="border border-slate-200 rounded-2xl overflow-hidden mb-8">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80">
                            <th class="px-5 py-4 text-xs font-bold text-slate-500 uppercase">Product</th>
                            <th class="px-5 py-4 text-xs font-bold text-slate-500 uppercase">SKU</th>
                            <th class="px-5 py-4 text-xs font-bold text-slate-500 uppercase text-right">Qty</th>
                            <th class="px-5 py-4 text-xs font-bold text-slate-500 uppercase text-right">Unit Price</th>
                            <th class="px-5 py-4 text-xs font-bold text-slate-500 uppercase text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($purchase->stocks && count($purchase->stocks) > 0)
                            @foreach($purchase->stocks as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-5 py-4 font-bold text-slate-800 text-sm">
                                        {{ $item->product->name ?? 'Deleted Product' }}
                                        @if($item->variation_names)
                                            <span class="block text-xs text-slate-500 font-normal mt-0.5">{{ $item->variation_names }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-mono text-slate-500">
                                        {{ $item->sku ?: '---' }}
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-slate-800 text-right">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-5 py-4 text-sm font-mono text-slate-600 text-right">
                                        {{ env('CURRENCY_SYMBOL', '₹') }}{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="px-5 py-4 font-bold font-mono text-slate-800 text-right">
                                        {{ env('CURRENCY_SYMBOL', '₹') }}{{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400 italic">No line items recorded.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($purchase->note)
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Notes</h4>
                    <div class="bg-amber-50 rounded-xl p-5 text-sm text-amber-900 border border-amber-100/50">
                        {{ $purchase->note }}
                    </div>
                </div>
            @endif

            <div class="mt-8 pt-8 border-t border-slate-100 flex justify-end">
                <div class="w-[300px] flex justify-between items-center bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <span class="font-bold text-slate-600">Grand Total</span>
                    <span class="text-2xl font-bold font-mono text-indigo-600">{{ env('CURRENCY_SYMBOL', '₹') }}{{ number_format($purchase->total, 2) }}</span>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
