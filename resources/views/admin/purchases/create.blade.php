@extends('layouts.admin')

@section('title', 'Create Purchase')

@section('content')
<div class="max-w-[1200px] mx-auto pb-12">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">New Purchase Order</h2>
            <p class="text-slate-500 mt-1">Record supplier purchases and add stock intake.</p>
        </div>
        <a href="{{ route('admin.purchases.index') }}" class="px-6 py-3 bg-white text-slate-600 font-bold rounded-2xl hover:bg-slate-50 border border-slate-300 transition-all shadow-sm">
            ← Back to Purchases
        </a>
    </div>

    @include('admin._alerts')

    <form id="purchaseForm" action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Details -->
            <div class="lg:col-span-1 space-y-6">
                <!-- PO Info -->
                <div class="bg-white rounded-[2rem] border border-slate-300 shadow-sm p-8">
                    <h3 class="text-lg font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Supply Details
                    </h3>
                    
                    <div class="space-y-5">
                        <div class="admin-form-group mb-0">
                            <label class="admin-label">Date *</label>
                            <input type="date" name="date" class="admin-input" required value="{{ old('date', date('Y-m-d')) }}">
                        </div>

                        <div class="admin-form-group mb-0">
                            <label class="admin-label">Supplier *</label>
                            <select name="supplier_id" class="admin-select" required>
                                <option value="">Select a vendor...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} {{ $supplier->company ? '('.$supplier->company.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="admin-form-group mb-0">
                            <label class="admin-label">Status *</label>
                            <select name="status" class="admin-select" required>
                                <option value="5" {{ old('status') == 5 ? 'selected' : '' }}>Pending</option>
                                <option value="10" {{ old('status') == 10 ? 'selected' : '' }}>Ordered</option>
                                <option value="15" {{ old('status', 15) == 15 ? 'selected' : '' }}>Received</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-2">Only <strong class="text-emerald-600">Received</strong> status updates the actual active inventory stock count for the frontend.</p>
                        </div>
                    </div>
                </div>

                <!-- Notes & Attachments -->
                <div class="bg-white rounded-[2rem] border border-slate-300 shadow-sm p-8">
                    <h3 class="text-lg font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Additional Info
                    </h3>
                    <div class="space-y-5">
                        <div class="admin-form-group mb-0">
                            <label class="admin-label">Notes</label>
                            <textarea name="note" class="admin-textarea" placeholder="Internal memo, delivery references...">{{ old('note') }}</textarea>
                        </div>
                        <div class="admin-form-group mb-0">
                            <label class="admin-label">Attachment (Invoice PDF/Image)</label>
                            <input type="file" name="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Line Items -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm p-8">
                    <div class="flex items-center justify-between gap-4 flex-wrap mb-6 pb-6 border-b border-slate-100">
                        <h3 class="text-xl font-bold font-outfit text-slate-900">Line Items</h3>
                        
                        <!-- Add product selector -->
                        <div class="flex items-center gap-3">
                            <select id="product-selector" class="admin-select !py-2.5 !w-[250px]">
                                <option value="">Select Product...</option>
                                @foreach($products as $prod)
                                    @if($prod->variations && $prod->variations->count() > 0)
                                        @foreach($prod->variations as $var)
                                            <option value="{{ $prod->id }}" data-is-variation="true" data-item-id="{{ $var->id }}" data-variation-names="{{ $var->productAttribute?->name }} {{ $var->productAttributeOption?->name }}" data-sku="{{ $var->sku }}" data-price="{{ $var->price > 0 ? $var->price : ($prod->buying_price ?: 0) }}" data-name="{{ $prod->name }} ({{ $var->productAttribute?->name ?: 'Variant' }}: {{ $var->productAttributeOption?->name ?: 'Option' }})">
                                                {{ $prod->name }} ({{ $var->productAttribute?->name ?: 'Variant' }}: {{ $var->productAttributeOption?->name ?: 'Option' }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $prod->id }}" data-is-variation="false" data-item-id="{{ $prod->id }}" data-sku="{{ $prod->sku }}" data-price="{{ $prod->buying_price ?: 0 }}" data-name="{{ $prod->name }}">
                                            {{ $prod->name }} (@ {{ env('CURRENCY_SYMBOL', '₹') }}{{ $prod->buying_price ?: 0 }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" onclick="addProductItem()" class="px-5 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-600 font-bold rounded-xl hover:bg-indigo-100 transition-all font-inter text-sm whitespace-nowrap">
                                Add
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto min-h-[250px]">
                        <table class="w-full text-left border-collapse" id="line-items-table">
                            <thead>
                                <tr class="bg-slate-50 rounded-xl">
                                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase rounded-l-xl">Product</th>
                                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase rounded-r-xl text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="line-items-body" class="divide-y divide-slate-100">
                                <tr id="empty-state">
                                    <td colspan="5" class="px-4 py-16 text-center text-slate-400 italic">No items added. Select a product above.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Block -->
                    <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                        <div class="w-full max-w-[300px] space-y-3 font-inter">
                            <div class="flex justify-between items-center text-sm text-slate-600">
                                <span>Subtotal:</span>
                                <span id="summary-subtotal" class="font-medium">{{ env('CURRENCY_SYMBOL', '₹') }}0.00</span>
                            </div>
                            <!-- Assuming simple total for now to keep reliable with backend -->
                            <div class="flex justify-between items-center text-lg font-bold text-slate-900 pt-3 border-t border-slate-100">
                                <span>Grand Total:</span>
                                <span id="summary-total" class="text-indigo-600">{{ env('CURRENCY_SYMBOL', '₹') }}0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Submission -->
                    <input type="hidden" name="products" id="products_input" value="[]">
                    <input type="hidden" name="total" id="total_input" value="0">

                    <div class="mt-10 pt-6 border-t border-slate-100 flex justify-end">
                        <button type="button" onclick="submitPurchase()" class="px-10 py-4 bg-indigo-600 text-white font-bold text-[15px] rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                            Save Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const CURRENCY_SYMBOL = '{{ env('CURRENCY_SYMBOL', '₹') }}';
    let poItems = [];

    function addProductItem() {
        const sel = document.getElementById('product-selector');
        const opt = sel.options[sel.selectedIndex];
        
        if (!opt || opt.value === '') {
            showAlert({ title: 'Select Product', message: 'Please select a product from the dropdown first.', type: 'warning' });
            return;
        }

        const id = opt.value;
        const itemId = opt.getAttribute('data-item-id');
        const isVariation = opt.getAttribute('data-is-variation') === 'true';
        const variationNames = opt.getAttribute('data-variation-names') || '';
        const name = opt.getAttribute('data-name');
        const price = parseFloat(opt.getAttribute('data-price') || 0);
        const sku = opt.getAttribute('data-sku') || '';

        // Generate simple unique ID
        const uid = 'item_' + Date.now() + Math.floor(Math.random() * 100);

        poItems.push({
            uid: uid,
            product_id: parseInt(id),
            item_id: parseInt(itemId),
            is_variation: isVariation,
            variation_names: variationNames,
            name: name,
            price: price,
            quantity: 1,
            total_discount: 0,
            total_tax: 0,
            subtotal: price,
            total: price,
            sku: sku,
            tax_id: [] 
        });

        sel.value = ""; // reset
        renderTable();
    }

    function removeProductItem(uid) {
        poItems = poItems.filter(i => i.uid !== uid);
        renderTable();
    }

    function updateItemDetails(uid, field, value) {
        let val = parseFloat(value);
        if (isNaN(val) || val < 0) val = 0;

        poItems = poItems.map(i => {
            if (i.uid === uid) {
                i[field] = val;
                i.subtotal = i.price * i.quantity;
                i.total = i.subtotal - i.total_discount + i.total_tax;
            }
            return i;
        });
        
        // Only re-render totals to avoid losing input focus
        calculateTotalsAndHiddenInputs();

        // Update display text dynamically in row
        const row = document.getElementById(`row-${uid}`);
        if(row) {
            const sub = poItems.find(x => x.uid === uid).total;
            document.getElementById(`row-${uid}`).querySelector('.row-subtotal').textContent = CURRENCY_SYMBOL + sub.toFixed(2);
        }
    }

    function renderTable() {
        const tbody = document.getElementById('line-items-body');
        
        if (poItems.length === 0) {
            tbody.innerHTML = `<tr id="empty-state"><td colspan="5" class="px-4 py-16 text-center text-slate-400 italic">No items added. Select a product above.</td></tr>`;
            calculateTotalsAndHiddenInputs();
            return;
        }

        let html = '';
        poItems.forEach(item => {
            html += `
                <tr id="row-${item.uid}" class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-4">
                        <div class="font-bold text-slate-800 text-[13px]">${item.name}</div>
                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">SKU: ${item.sku || 'N/A'}</div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-sm">${CURRENCY_SYMBOL}</span>
                            <input type="number" step="0.01" value="${item.price}" 
                                onchange="updateItemDetails('${item.uid}', 'price', this.value)" 
                                onkeyup="updateItemDetails('${item.uid}', 'price', this.value)"
                                class="w-24 pl-6 pr-2 py-1.5 text-sm border border-slate-200 rounded-lg outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all font-mono">
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <input type="number" min="1" step="1" value="${item.quantity}" 
                            onchange="updateItemDetails('${item.uid}', 'quantity', this.value)"
                            onkeyup="updateItemDetails('${item.uid}', 'quantity', this.value)"
                            class="w-20 px-3 py-1.5 text-sm border border-slate-200 rounded-lg outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all bg-slate-50 text-center font-bold">
                    </td>
                    <td class="px-4 py-4 text-right">
                        <span class="row-subtotal font-bold text-slate-700 font-mono text-[14px]">${CURRENCY_SYMBOL}${item.total.toFixed(2)}</span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <button type="button" onclick="removeProductItem('${item.uid}')" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        calculateTotalsAndHiddenInputs();
    }

    function calculateTotalsAndHiddenInputs() {
        let subtotal = 0;
        let total = 0;

        poItems.forEach(i => {
            subtotal += i.subtotal;
            total += i.total;
        });

        document.getElementById('summary-subtotal').textContent = CURRENCY_SYMBOL + subtotal.toFixed(2);
        document.getElementById('summary-total').textContent = CURRENCY_SYMBOL + total.toFixed(2);

        document.getElementById('products_input').value = JSON.stringify(poItems);
        document.getElementById('total_input').value = total.toFixed(2);
    }

    function submitPurchase() {
        if(poItems.length === 0) {
            showAlert({ title: 'No Items', message: 'Please add at least one line item to the purchase order before saving.', type: 'warning' });
            return;
        }
        
        calculateTotalsAndHiddenInputs();
        document.getElementById('purchaseForm').submit();
    }
</script>
@endpush
