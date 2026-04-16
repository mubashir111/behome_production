@extends('layouts.admin')

@section('title', 'Edit Product')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    #crop-image-element { max-width: 100%; display: block; }
    .crop-area-wrapper {
        min-height: 300px;
        max-height: calc(90vh - 200px);
        width: 100%;
        overflow: hidden;
        background-color: #f8fafc;
        border-radius: 0.75rem;
    }
    .pe-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(to right, #f8fafc, #ffffff);
    }
    .pe-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pe-section-title {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .pe-section-subtitle {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 1px;
        font-weight: 500;
    }
    .status-toggle-card {
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s;
        cursor: default;
    }
    .status-toggle-card:hover { border-color: #c7d2fe; background: #f8f7ff; }
    .field-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        display: block;
    }
    .field-input {
        width: 100%;
        padding: 9px 14px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: 13.5px;
        color: #1e293b;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;
    }
    .field-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .block-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid;
        cursor: pointer;
        transition: all 0.15s;
    }
    .block-add-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .save-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(12px);
        border-top: 1px solid #e2e8f0;
        padding: 12px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 100;
        box-shadow: 0 -4px 24px rgba(0,0,0,0.07);
    }
    @media(min-width:768px) { .save-bar { left: 256px; } }
</style>
@endpush

@section('content')
@php
    $p_name = old('name', $product->name);
    $p_sku = old('sku', $product->sku);
    $p_category = old('product_category_id', $product->product_category_id);
    $p_brand = old('product_brand_id', $product->product_brand_id);
    $p_unit = old('unit_id', $product->unit_id);
    $p_buying_price = old('buying_price', $product->buying_price);
    $p_selling_price = old('selling_price', $product->selling_price);
    $p_weight = old('weight', $product->weight);
    $p_barcode = old('barcode_id', $product->barcode_id);
    $p_status = old('status', $product->status);
    $p_purchasable = old('can_purchasable', $product->can_purchasable);
    $p_stock_out = old('show_stock_out', $product->show_stock_out);
    $p_refundable = old('refundable', $product->refundable);
    $p_max_qty = old('maximum_purchase_quantity', $product->maximum_purchase_quantity);
    $p_low_stock = old('low_stock_quantity_warning', $product->low_stock_quantity_warning);
    $p_description = old('description', $product->description);
    
    if (is_array($product->tags)) {
        $p_tags = old('tags_string', implode(', ', collect($product->tags)->pluck('name')->toArray()));
    } else {
        $p_tags = old('tags_string', implode(', ', $product->tags->pluck('name')->toArray()));
    }
    
    $p_taxes = old('tax_id', $product->taxes->pluck('tax_id')->toArray());
@endphp

<div class="max-w-[1240px] mx-auto pb-28 px-4 sm:px-6 lg:px-8">
    <!-- Premium Header -->
    <div class="mb-8">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <a href="{{ route('admin.products.index') }}" class="hover:text-indigo-600 transition-colors font-medium">Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            <span class="text-slate-600 font-semibold truncate max-w-[240px]">{{ $p_name }}</span>
        </div>
        <!-- Title row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-tight">Edit Product</h1>
                    <p class="text-sm text-slate-500 font-medium mt-0.5 truncate max-w-[300px]">{{ $p_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">&larr; Back</a>
                <button type="button" onclick="document.getElementById('product-form').submit()" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold rounded-xl hover:from-indigo-500 hover:to-violet-500 transition-all shadow-lg shadow-indigo-200 active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 space-y-8">
                <!-- Product Details Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#eef2ff;">
                            <svg class="w-4 h-4" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">General Information</div>
                            <div class="pe-section-subtitle">Product name, SKU, description</div>
                        </div>
                    </div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<div class="p-6 space-y-6">
                        <div class="space-y-1.5">
                            <label for="name" class="text-sm font-medium text-slate-700">Product Title</label>
                            <input type="text" name="name" id="name" value="{{ $p_name }}" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                   placeholder="e.g. Wireless Charging Pad">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label for="sku" class="text-sm font-medium text-slate-700">SKU / Item Code</label>
                                <input type="text" name="sku" id="sku" value="{{ $p_sku }}" required
                                       class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                       placeholder="PROD-001">
                            </div>
                            <div class="space-y-1.5">
                                <label for="barcode_id" class="text-sm font-medium text-slate-700">Barcode Profile</label>
                                <select name="barcode_id" id="barcode_id" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]"
                                        style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364748b%22 stroke-width=%222%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E');">
                                    @foreach($barcodes as $barcode)
                                        <option value="{{ $barcode->id }}" {{ $p_barcode == $barcode->id ? 'selected' : '' }}>{{ $barcode->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="description" class="text-sm font-medium text-slate-700">Detailed Description</label>
                            <textarea name="description" id="description" rows="8"
                                      class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                      placeholder="Write something compelling about this product...">{{ $p_description }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Media Section -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#f0fdf4;">
                            <svg class="w-4 h-4" style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Product Media</div>
                            <div class="pe-section-subtitle">Gallery images and thumbnails</div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-sm text-slate-600">Upload new image files to add them to the current product gallery.</p>
                            <p class="text-xs text-slate-400 mt-1">JPG, PNG or WebP. Maximum 4MB per image.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                            @forelse($product->getMedia('product') as $image)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ $image->hasGeneratedConversion('thumb') ? $image->getUrl('thumb') : $image->getUrl() }}" class="w-full h-full object-contain" alt="{{ $product->name }}">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="px-2 py-0.5 bg-white rounded text-[10px] font-bold text-slate-900">{{ $loop->first ? 'Main' : 'Gallery' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-3">
                                        <label class="flex h-11 cursor-pointer items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition-all hover:border-indigo-300 hover:text-indigo-600">
                                            Crop & Replace
                                            <input type="file" accept="image/*" class="hidden" onchange="startCropping('gallery_replace', {{ $loop->index }}, this)">
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" onclick="deleteGalleryImage('{{ route('admin.products.images.delete', ['product' => $product, 'index' => $loop->index]) }}')" class="w-full rounded-lg bg-rose-50 px-3 py-2.5 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-100">Delete This Image</button>
                                    </div>
                                </div>
                            @empty
                                <div class="aspect-square rounded-lg border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-xs font-medium text-slate-400">
                                    No image uploaded
                                </div>
                            @endforelse
                        </div>
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <label class="block cursor-pointer">
                                    <div class="flex min-h-[120px] w-full md:w-[220px] rounded-xl border-2 border-dashed border-slate-200 bg-white flex-col items-center justify-center hover:bg-slate-50 hover:border-indigo-300 transition-all group">
                                        <svg class="w-6 h-6 text-slate-300 group-hover:text-indigo-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-indigo-600">Crop & Add Image</span>
                                    </div>
                                    <input type="file" accept="image/*" class="hidden" onchange="startCropping('gallery_add', null, this)">
                                </label>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-700">Add new gallery image</p>
                                    <p class="mt-1 text-xs text-slate-500">Crop and append a new image to the product gallery immediately.</p>
                                </div>
                            </div>
                        </div>
                        @error('images') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                        @error('images.*') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Shipping Section -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#fff7ed;">
                            <svg class="w-4 h-4" style="color:#f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7h-8m8 0l-4-4m4 4l-4 4M4 17h8m-8 0l4 4m-4-4l4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Shipping & Logistics</div>
                            <div class="pe-section-subtitle">Weight, units, shipping cost</div>
                        </div>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="weight" class="text-sm font-medium text-slate-700">Weight (kg)</label>
                            <input type="text" name="weight" id="weight" value="{{ $p_weight }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                   placeholder="0.00">
                        </div>
                        <div class="space-y-1.5">
                            <label for="unit_id" class="text-sm font-medium text-slate-700">Unit of Measure</label>
                            <select name="unit_id" id="unit_id" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]"
                                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364748b%22 stroke-width=%222%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E');">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $p_unit == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
 
                <!-- Detailed Content Section -->
                <div class="bg-white rounded-2xl rounded-b-none border border-slate-200 shadow-sm overflow-hidden border-b-0">
                    <div class="pe-section-header" style="justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="pe-section-icon" style="background:#fdf4ff;">
                                <svg class="w-4 h-4" style="color:#a855f7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pe-section-title">Specifications & Content</div>
                                <div class="pe-section-subtitle">Attributes, return policy</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Additional Info (Dynamic Key-Value) -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-slate-700">Additional Information (Specifications)</label>
                                <button type="button" onclick="addInfoRow()" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    Add Attribute
                                </button>
                            </div>
                            
                            <div id="additional-info-container" class="space-y-3">
                                <!-- Dynamic rows here -->
                            </div>
                            <input type="hidden" name="additional_info" id="additional_info_hidden">
                            
                            <p class="text-[11px] text-slate-400 font-medium">Add key-value pairs like "Material: Leather" or "Format: 80x80cm". These appear in the 'Additional Information' tab.</p>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Shipping & Return Textarea -->
                        <div class="space-y-1.5">
                            <label for="shipping_and_return" class="text-sm font-medium text-slate-700">Shipping & Return Policy</label>
                            <textarea name="shipping_and_return" id="shipping_and_return" rows="5"
                                      class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                      placeholder="Explain your shipping and return policy for this product...">{{ old('shipping_and_return', $product->shipping_and_return) }}</textarea>
                            <p class="text-[11px] text-slate-400 font-medium">This content appears in the 'Shipping and Return' tab.</p>
                        </div>
                    </div>
                </div>

                <!-- Rich Content Blocks Section -->
                <div class="bg-white rounded-2xl rounded-t-none border border-slate-200 shadow-sm overflow-hidden -mt-px">
                    <div class="pe-section-header" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="pe-section-icon" style="background:#fff1f2;">
                                <svg class="w-4 h-4" style="color:#f43f5e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <div class="pe-section-title">Rich Content Layout</div>
                                <div class="pe-section-subtitle">Visual blocks for the product description area</div>
                            </div>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            <button type="button" onclick="addBlock('feature_split')" class="block-add-btn" style="background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Feature Split
                            </button>
                            <button type="button" onclick="addBlock('hero_banner')" class="block-add-btn" style="background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Hero Banner
                            </button>
                            <button type="button" onclick="addBlock('icon_grid')" class="block-add-btn" style="background:#fff7ed;color:#ea580c;border-color:#fed7aa;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Icon Grid
                            </button>
                        </div>
                    </div>
                    
                    <div id="content-blocks-container" class="divide-y divide-slate-100">
                        <!-- Dynamic Blocks -->
                    </div>
                    
                    <div class="p-6 bg-slate-50/30 border-t border-slate-100">
                        <p class="text-[11px] text-slate-400 font-medium text-center">These blocks allow you to create rich, visually engaging product stories. Every block is responsive and theme-aware.</p>
                    </div>
                    <input type="hidden" name="details" id="details_hidden">
                </div>
            </div>

            <!-- Image Cropping Modal -->
            <div id="crop-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col animate-in fade-in zoom-in duration-200" style="max-height:90vh;">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Crop Block Image</h3>
                            <p class="text-[10px] text-slate-500 font-medium">Ensures professional quality (800x600 recommended)</p>
                        </div>
                        <button type="button" onclick="closeCropModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </button>
                    </div>
                    <div class="p-6 flex-1 min-h-0 overflow-y-auto">
                        <div class="crop-area-wrapper flex items-center justify-center">
                            <img id="crop-image-element" src="" alt="To crop">
                        </div>
                        <div class="flex items-center justify-between mt-6">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="cropper.rotate(-90)" class="p-2 bg-slate-100 rounded-lg hover:bg-slate-200 text-slate-600 transition-colors" title="Rotate Left">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </button>
                                <button type="button" onclick="cropper.rotate(90)" class="p-2 bg-slate-100 rounded-lg hover:bg-slate-200 text-slate-600 transition-colors" title="Rotate Right">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="closeCropModal()" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors">Cancel</button>
                                <button type="button" id="save-crop-btn" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2">
                                    <span id="crop-btn-spinner" class="hidden animate-spin w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                                    <span>Apply & Upload</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Settings -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Status & Visibility -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#{{ $p_status == 5 ? 'f0fdf4' : 'fff1f2' }};">
                            <svg class="w-4 h-4" style="color:#{{ $p_status == 5 ? '22c55e' : 'f43f5e' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Visibility &amp; Status</div>
                            <div class="pe-section-subtitle">Control where this product appears</div>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">

                        {{-- TOGGLE MACRO: uses JS to drive visual state; no Tailwind peer-checked needed --}}
                        @php
                        function renderToggle(string $id, string $name, bool $checked, string $label, string $sublabel, string $iconBg, string $iconColor, string $iconPath): string {
                            $track   = $checked ? 'background:#6366f1;' : 'background:#e2e8f0;';
                            $knob    = $checked ? 'transform:translateX(20px);' : '';
                            return ''; // rendered inline below
                        }
                        @endphp

                        {{-- Active --}}
                        <div class="status-toggle-card" onclick="toggleSwitch('status_toggle')">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#1e293b;">Active</div>
                                    <div style="font-size:11px;color:#94a3b8;">Show product in the store</div>
                                </div>
                            </div>
                            <div style="position:relative;pointer-events:none;">
                                <input type="hidden" name="status" value="10">
                                <input type="checkbox" name="status" value="5" {{ $p_status == 5 ? 'checked' : '' }} class="sr-only" id="status_toggle">
                                <div id="status_toggle_track" style="display:block;width:44px;height:24px;{{ $p_status == 5 ? 'background:#6366f1;' : 'background:#e2e8f0;' }}border-radius:12px;position:relative;transition:background 0.2s;">
                                    <span id="status_toggle_knob" style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:10px;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.15);{{ $p_status == 5 ? 'transform:translateX(20px);' : '' }}"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Purchasable --}}
                        <div class="status-toggle-card" onclick="toggleSwitch('purchasable_toggle')">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4" style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#1e293b;">Purchasable</div>
                                    <div style="font-size:11px;color:#94a3b8;">Allow customers to buy it</div>
                                </div>
                            </div>
                            <div style="position:relative;pointer-events:none;">
                                <input type="hidden" name="can_purchasable" value="10">
                                <input type="checkbox" name="can_purchasable" value="5" {{ $p_purchasable == 5 ? 'checked' : '' }} class="sr-only" id="purchasable_toggle">
                                <div id="purchasable_toggle_track" style="display:block;width:44px;height:24px;{{ $p_purchasable == 5 ? 'background:#6366f1;' : 'background:#e2e8f0;' }}border-radius:12px;position:relative;transition:background 0.2s;">
                                    <span id="purchasable_toggle_knob" style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:10px;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.15);{{ $p_purchasable == 5 ? 'transform:translateX(20px);' : '' }}"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Show When Out-of-Stock --}}
                        <div class="status-toggle-card" onclick="toggleSwitch('stock_out_toggle')">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4" style="color:#f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#1e293b;">Show When Out-of-Stock</div>
                                    <div style="font-size:11px;color:#94a3b8;">Display even if no stock</div>
                                </div>
                            </div>
                            <div style="position:relative;pointer-events:none;">
                                <input type="hidden" name="show_stock_out" value="10">
                                <input type="checkbox" name="show_stock_out" value="5" {{ $p_stock_out == 5 ? 'checked' : '' }} class="sr-only" id="stock_out_toggle">
                                <div id="stock_out_toggle_track" style="display:block;width:44px;height:24px;{{ $p_stock_out == 5 ? 'background:#6366f1;' : 'background:#e2e8f0;' }}border-radius:12px;position:relative;transition:background 0.2s;">
                                    <span id="stock_out_toggle_knob" style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:10px;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.15);{{ $p_stock_out == 5 ? 'transform:translateX(20px);' : '' }}"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Refundable --}}
                        <div class="status-toggle-card" onclick="toggleSwitch('refundable_toggle')">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4" style="color:#8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:#1e293b;">Refundable</div>
                                    <div style="font-size:11px;color:#94a3b8;">Is this item refundable?</div>
                                </div>
                            </div>
                            <div style="position:relative;pointer-events:none;">
                                <input type="hidden" name="refundable" value="10">
                                <input type="checkbox" name="refundable" value="5" {{ $p_refundable == 5 ? 'checked' : '' }} class="sr-only" id="refundable_toggle">
                                <div id="refundable_toggle_track" style="display:block;width:44px;height:24px;{{ $p_refundable == 5 ? 'background:#6366f1;' : 'background:#e2e8f0;' }}border-radius:12px;position:relative;transition:background 0.2s;">
                                    <span id="refundable_toggle_knob" style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:10px;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.15);{{ $p_refundable == 5 ? 'transform:translateX(20px);' : '' }}"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#fefce8;">
                            <svg class="w-4 h-4" style="color:#ca8a04;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Pricing</div>
                            <div class="pe-section-subtitle">Selling price and cost</div>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-1.5">
                            <label for="selling_price" class="text-sm font-medium text-slate-700">Selling Price</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-bold text-xs">$</span>
                                </div>
                                <input type="number" step="0.01" name="selling_price" id="selling_price" value="{{ number_format($p_selling_price, 2, '.', '') }}" required
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm font-bold">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label for="buying_price" class="text-sm font-medium text-slate-700">Cost per Item</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-bold text-xs">$</span>
                                </div>
                                <input type="number" step="0.01" name="buying_price" id="buying_price" value="{{ number_format($p_buying_price, 2, '.', '') }}" required
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm">
                            </div>
                            <p class="text-[10px] text-slate-400">Not visible to customers.</p>
                        </div>
                    </div>
                </div>

                @include('admin.products._offer_card')

                <!-- Organization -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#f0f9ff;">
                            <svg class="w-4 h-4" style="color:#0ea5e9;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Organization</div>
                            <div class="pe-section-subtitle">Category, brand, and tags</div>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-1.5">
                            <label for="product_category_id" class="text-sm font-medium text-slate-700">Category</label>
                            <select name="product_category_id" id="product_category_id" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]"
                                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364748b%22 stroke-width=%222%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E');">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $p_category == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="product_brand_id" class="text-sm font-medium text-slate-700">Brand</label>
                            <select name="product_brand_id" id="product_brand_id"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]"
                                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%2364748b%22 stroke-width=%222%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 d=%22M19 9l-7 7-7-7%22 /%3E%3C/svg%3E');">
                                <option value="">No Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ $p_brand == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="tags_input" class="text-sm font-medium text-slate-700">Tags</label>
                            <input type="text" id="tags_input" value="{{ $p_tags }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm"
                                   placeholder="seasonal, bestseller">
                            <input type="hidden" name="tags" id="tags_hidden">
                        </div>
                    </div>
                </div>

                <!-- Tax Profiles -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="pe-section-header">
                        <div class="pe-section-icon" style="background:#fdf4ff;">
                            <svg class="w-4 h-4" style="color:#a855f7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <div class="pe-section-title">Taxation</div>
                            <div class="pe-section-subtitle">Apply tax profiles to this product</div>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($taxes as $tax)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer group">
                                <input type="checkbox" name="tax_id[]" value="{{ $tax->id }}" {{ in_array($tax->id, $p_taxes) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-700">{{ $tax->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $tax->tax_rate }}% Rate</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- Product Variants Section (full width below main grid) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-8">
            <div class="pe-section-header" style="justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="pe-section-icon" style="background:#ecfdf5;">
                        <svg class="w-4 h-4" style="color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <div>
                        <div class="pe-section-title">Product Variants</div>
                        <div class="pe-section-subtitle">Colors, sizes and other options</div>
                    </div>
                </div>
                <button type="button" id="add-variant-btn" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl text-xs font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Add Variant
                </button>
            </div>
            <div class="p-6">
                <div id="variants-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Variants will be loaded here via AJAX -->
                    <div class="col-span-full text-center py-16">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h5m-12 4h12a2 2 0 002-2V9a2 2 0 00-2-2h-2.343" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <p class="text-sm font-medium text-slate-600 mb-2">No variants yet</p>
                        <p class="text-xs text-slate-500 mb-4">Add your first product variant (color, size, etc.)</p>
                        <button type="button" onclick="openAddVariantModal()" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 text-sm font-semibold transition-all inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Create First Variant
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Save Bar -->
        <div class="save-bar">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:8px;height:8px;border-radius:50%;background:#22c55e;"></div>
                <span style="font-size:12px;color:#64748b;font-weight:500;">All changes will be saved when you click Save Changes</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="{{ route('admin.products.index') }}" style="padding:9px 18px;font-size:13px;font-weight:600;color:#64748b;background:#f1f5f9;border-radius:10px;text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">← Discard</a>
                <button type="button" onclick="document.getElementById('product-form').submit()" style="padding:9px 22px;font-size:13px;font-weight:700;color:white;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:10px;cursor:pointer;transition:all 0.15s;box-shadow:0 4px 14px rgba(99,102,241,0.35);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                    ✓ Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Add/Edit Variant Modal -->
<div id="variant-modal" class="hidden fixed inset-0 bg-slate-900/60 z-[100] flex justify-center items-start overflow-y-auto py-10 px-4">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full my-auto flex flex-col">
        <div class="sticky top-0 z-20 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-slate-200 px-6 py-5 flex items-center justify-between rounded-t-2xl">
            <div>
                <h3 class="font-bold text-lg text-slate-900" id="modal-title">Add Variant</h3>
                <p class="text-xs text-slate-500 mt-0.5">Add a new product variant option</p>
            </div>
            <button type="button" onclick="closeVariantModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-light leading-none">&times;</button>
        </div>
        <form id="variant-form" class="p-6 space-y-5">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700 block">
                    <span class="inline-block mb-2">Attribute Type</span>
                    <span class="text-red-500">*</span>
                </label>
                <select id="variant-attribute" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm font-medium bg-white cursor-pointer">
                    <option value="">Choose an attribute...</option>
                    <option value="Color">🎨 Color</option>
                    <option value="Size">📏 Size</option>
                    <option value="Material">🧵 Material</option>
                    <option value="Brand">🏷️ Brand</option>
                    <option value="Style">✨ Style</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700 block">
                    <span class="inline-block mb-2">Attribute Value</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" id="variant-value" placeholder="e.g., Red, Large, Cotton, Premium" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm">
            </div>

            <!-- Color Image Upload (appears only for Color attribute) -->
            <div id="color-image-section" class="hidden space-y-2 p-4 rounded-lg bg-indigo-50 border border-indigo-200">
                <label class="text-sm font-semibold text-slate-700 block">
                    <span class="inline-block mb-2">📸 Color Image</span>
                    <span class="text-red-500">* Required for colors</span>
                </label>
                <div class="space-y-3">
                    <div id="color-image-preview" class="hidden">
                        <div class="relative inline-block">
                            <img id="color-image-preview-img" src="" alt="Color preview" class="w-full h-40 rounded-lg object-cover border-2 border-indigo-300">
                            <button type="button" onclick="clearColorImage()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 shadow">✕</button>
                        </div>
                    </div>
                    <label id="color-image-btn" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg border-2 border-dashed border-indigo-300 text-indigo-700 font-semibold hover:bg-indigo-100 transition-all text-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <span id="color-image-btn-text">📁 Choose & Crop Image</span>
                        <input type="file" id="color-image-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="startCropping('variant_color', null, this)">
                    </label>
                    <p class="text-xs text-slate-500">Image will be cropped before uploading. JPG/PNG/WebP, max 2MB</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700 block">Price Constraint (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-slate-500 text-sm">$</span>
                        <input type="number" id="variant-price" placeholder="0.00" step="0.01" min="0" class="w-full pl-6 pr-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700 block">SKU Code</label>
                    <input type="text" id="variant-sku" placeholder="e.g., PROD-RED-L" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all text-sm font-mono text-xs">
                </div>
            </div>
            
            <div class="bg-amber-50 rounded-lg p-3 text-xs text-amber-700 font-medium">
                <span class="font-bold flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg> Stock Management:</span> 
                Initial stock is always 0. You must create a formal "Purchase Order" in the Purchases tab to add inventory stock legally.
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeVariantModal()" class="flex-1 px-4 py-3 rounded-lg border-2 border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold hover:from-indigo-700 hover:to-indigo-800 transition-all shadow-md">Save Variant</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleSwitch(id) {
        const cb = document.getElementById(id);
        cb.checked = !cb.checked;
        const track = document.getElementById(id + '_track');
        const knob  = document.getElementById(id + '_knob');
        if (cb.checked) {
            track.style.background = '#6366f1';
            knob.style.transform   = 'translateX(20px)';
        } else {
            track.style.background = '#e2e8f0';
            knob.style.transform   = '';
        }
    }

    const productId = {{ $product->id }};
    let currentVariants = [];

    // Close modal
    function closeVariantModal() {
        document.getElementById('variant-modal').classList.add('hidden');
        document.getElementById('variant-attribute').disabled = false;
        document.getElementById('variant-value').disabled = false;
    }

    // Load variants
    async function loadVariants() {
        try {
            const response = await fetch(`/admin/products/${productId}/variations`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${window.localStorage.getItem('auth_token') || ''}`,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'include'
            });
            const data = await response.json();
            displayVariants(data.data || []);
        } catch (error) {
            console.error('Error loading variants:', error);
            document.getElementById('variants-list').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-sm text-red-500">Error loading variants</p>
                </div>
            `;
        }
    }

    // Display variants
    function displayVariants(variants) {
        currentVariants = variants || [];
        const container = document.getElementById('variants-list');
        if (!variants || variants.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-center py-12 text-slate-500">
                    <p class="text-sm mb-4">No variants yet</p>
                    <button type="button" onclick="openAddVariantModal()" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-sm font-semibold transition-all">
                        Create First Variant
                    </button>
                </div>
            `;
            return;
        }

        container.innerHTML = variants.map(variant => {
            const attrIcon = {
                'Color': '🎨',
                'Size': '📏',
                'Material': '🧵',
                'Brand': '🏷️',
                'Style': '✨'
            };
            const icon = attrIcon[variant.product_attribute_name] || '📦';
            const stockColor = variant.stock > 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';
            const hasImage = variant.media && variant.media.length > 0;
            const imageUrl = hasImage ? variant.media[0].original_url : null;
            
            return `
            <div class="flex flex-col rounded-xl border-2 border-slate-200 bg-white hover:border-indigo-300 hover:shadow-md transition-all overflow-hidden">
                <div class="w-full aspect-square overflow-hidden flex items-center justify-center border-b border-slate-100 ${variant.product_attribute_name === 'Color' ? 'bg-slate-100' : 'bg-gradient-to-br from-indigo-50 to-blue-50'}">
                    ${hasImage && variant.product_attribute_name === 'Color'
                        ? `<img src="${imageUrl}" alt="${escapeHtml(variant.product_attribute_option_name)}" class="w-full h-full object-cover">`
                        : `<span class="text-5xl">${icon}</span>`
                    }
                </div>
                <div class="p-3 flex-1 flex flex-col gap-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">${escapeHtml(variant.product_attribute_name)}</p>
                    <p class="font-semibold text-slate-800 text-sm leading-tight">${escapeHtml(variant.product_attribute_option_name)}</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700">💵 ${parseFloat(variant.price) > 0 ? parseFloat(variant.price).toFixed(2) : (variant.price || 0)}</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded ${stockColor}">
                            ${variant.stock > 0 ? `${variant.stock} in stock` : 'Out of Stock'}
                        </span>
                    </div>
                    ${variant.sku ? `<p class="text-[10px] font-mono text-slate-400 mt-0.5">${escapeHtml(variant.sku)}</p>` : ''}
                </div>
                <div class="flex border-t border-slate-100">
                    <button type="button" onclick="openEditVariantModal(${variant.id})" class="flex-1 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 transition-all border-r border-slate-100">
                        ✎ Edit
                    </button>
                    <button type="button" onclick="deleteVariant(${variant.id})" class="flex-1 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-all">
                        ✕ Remove
                    </button>
                </div>
            </div>
            `;
        }).join('');
    }

    // Open add modal
    function openAddVariantModal() {
        document.getElementById('modal-title').textContent = 'Add Variant';
        document.getElementById('variant-form').reset();
        const form = document.getElementById('variant-form');
        form.dataset.mode = 'create';
        delete form.dataset.variantId;
        document.getElementById('variant-attribute').disabled = false;
        document.getElementById('variant-value').disabled = false;
        document.getElementById('color-image-section').classList.add('hidden');
        clearColorImage();
        document.getElementById('variant-modal').classList.remove('hidden');
    }

    // Open edit modal
    function openEditVariantModal(variantId) {
        const variant = currentVariants.find(v => v.id == variantId);
        if (!variant) return;

        document.getElementById('modal-title').textContent = 'Edit Variant';
        document.getElementById('variant-form').reset();

        const form = document.getElementById('variant-form');
        form.dataset.mode = 'edit';
        form.dataset.variantId = variantId;

        const attrSelect = document.getElementById('variant-attribute');
        attrSelect.value = variant.product_attribute_name || '';
        attrSelect.disabled = true;

        const valInput = document.getElementById('variant-value');
        valInput.value = variant.product_attribute_option_name || '';
        valInput.disabled = true;

        document.getElementById('variant-price').value = parseFloat(variant.price) || '';
        document.getElementById('variant-sku').value = variant.sku || '';

        const colorSection = document.getElementById('color-image-section');
        if (variant.product_attribute_name === 'Color') {
            colorSection.classList.remove('hidden');
            // Show existing image preview if available
            if (variant.media && variant.media.length > 0) {
                document.getElementById('color-image-preview').classList.remove('hidden');
                document.getElementById('color-image-preview-img').src = variant.media[0].original_url;
                document.getElementById('color-image-btn-text').textContent = '🔄 Replace Image';
            }
        } else {
            colorSection.classList.add('hidden');
        }

        document.getElementById('variant-modal').classList.remove('hidden');
    }

    // Delete variant
    async function deleteVariant(variationId) {
        showConfirm({
            title: 'Delete Variant',
            message: 'Are you sure you want to delete this variant? This action cannot be undone.',
            confirmText: 'Yes, Delete',
            type: 'danger',
            onConfirm: async () => {
                try {
                    const response = await fetch(`/admin/products/${productId}/variations/destroy/${variationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${window.localStorage.getItem('auth_token') || ''}`,
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        credentials: 'include'
                    });
                    if (response.ok) {
                        showToast('Variant deleted successfully', 'success');
                        loadVariants();
                    }
                } catch (error) {
                    console.error('Error deleting variant:', error);
                    showToast('Error deleting variant', 'error');
                }
            }
        });
    }

    // Handle color attribute change
    document.getElementById('variant-attribute').addEventListener('change', (e) => {
        const colorSection = document.getElementById('color-image-section');
        if (e.target.value === 'Color') {
            colorSection.classList.remove('hidden');
        } else {
            colorSection.classList.add('hidden');
            clearColorImage();
        }
    });

    // Color image preview is handled by the cropper save callback

    // Clear color image
    function clearColorImage() {
        document.getElementById('color-image-input').value = '';
        document.getElementById('color-image-preview').classList.add('hidden');
        document.getElementById('color-image-btn-text').textContent = '📁 Choose Image';
    }

    // Save variant (create or edit)
    document.getElementById('variant-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const isEdit = form.dataset.mode === 'edit';
        const variantId = form.dataset.variantId;

        const attribute = document.getElementById('variant-attribute').value;
        const value = document.getElementById('variant-value').value;
        const price = parseFloat(document.getElementById('variant-price').value) || 0;
        const sku = document.getElementById('variant-sku').value;
        const colorImage = document.getElementById('color-image-input').files[0];

        // For new Color variants, image is required; for edits with existing image, optional
        if (!isEdit && attribute === 'Color' && !colorImage) {
            showToast('Please upload a color image', 'error');
            return;
        }

        try {
            const formData = new FormData();

            if (isEdit) {
                formData.append('price', price);
                formData.append('sku', sku);
                if (colorImage) formData.append('variant_image', colorImage);

                const response = await fetch(`/admin/products/${productId}/variations/update-simple/${variantId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${window.localStorage.getItem('auth_token') || ''}`,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'include',
                    body: formData
                });

                const data = await response.json();
                if (response.ok) {
                    showToast('Variant updated successfully! ✓', 'success');
                    closeVariantModal();
                    loadVariants();
                } else {
                    showToast(data.message || data.error || 'Error updating variant', 'error');
                }
            } else {
                formData.append('product_attribute_name', attribute);
                formData.append('product_attribute_option_name', value);
                formData.append('price', price);
                formData.append('sku', sku);
                formData.append('stock', 0);
                if (colorImage) formData.append('variant_image', colorImage);

                const response = await fetch(`/admin/products/${productId}/variations/store`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${window.localStorage.getItem('auth_token') || ''}`,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'include',
                    body: formData
                });

                const data = await response.json();
                if (response.ok) {
                    showToast('Variant created successfully! ✓', 'success');
                    closeVariantModal();
                    loadVariants();
                } else {
                    showToast(data.message || data.error || 'Error creating variant', 'error');
                }
            }
        } catch (error) {
            console.error('Error saving variant:', error);
            showToast('Error saving variant: ' + error.message, 'error');
        }
    });

    // Utility
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        let bgColor = 'bg-white';
        let textColor = 'text-slate-800';
        let iconColor = 'text-indigo-500';
        let icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>';

        if (type === 'success') {
            iconColor = 'text-emerald-500';
            icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>';
        } else if (type === 'error') {
            iconColor = 'text-rose-500';
            icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>';
        }

        toast.className = `
            pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border border-white/20
            ${bgColor} ${textColor}
            translate-x-full transition-all duration-300 ease-out opacity-0
            backdrop-blur-md bg-opacity-95
        `;
        
        toast.innerHTML = `
            <div class="${iconColor} flex-shrink-0">${icon}</div>
            <div class="text-sm font-semibold tracking-tight">${message}</div>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // --- Rich Content Blocks Logic ---
    const blocksContainer = document.getElementById('content-blocks-container');
    const detailsHidden = document.getElementById('details_hidden');
    let blocksData = @json($product->details ?? []);
    if (!Array.isArray(blocksData)) blocksData = [];

    function renderBlocks() {
        blocksContainer.innerHTML = '';
        if (blocksData.length === 0) {
            blocksContainer.innerHTML = `
                <div class="px-6 py-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-600">No content blocks yet</h3>
                    <p class="text-xs text-slate-400 mt-1">Start building your product story by adding a block above.</p>
                </div>
            `;
            detailsHidden.value = '[]';
            return;
        }

        blocksData.forEach((block, index) => {
            const blockEl = document.createElement('div');
            blockEl.className = 'p-6 hover:bg-slate-50/50 transition-colors relative group';
            
            let typeLabel = '';
            let fieldsHtml = '';

            if (block.type === 'feature_split') {
                typeLabel = 'Feature Split';
                fieldsHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Label (Small text above title)</label>
                                <input type="text" value="${escapeHtml(block.label || '')}" onchange="updateBlockField(${index}, 'label', this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Main Title</label>
                                <input type="text" value="${escapeHtml(block.title || '')}" onchange="updateBlockField(${index}, 'title', this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Description</label>
                                <textarea onchange="updateBlockField(${index}, 'text', this.value)" rows="3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm">${escapeHtml(block.text || '')}</textarea>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Image Selection</label>
                                ${block.image ? `
                                <div class="mb-3 relative group">
                                    <img src="${escapeHtml(block.image)}" class="w-full h-40 object-cover rounded-lg border border-slate-200 shadow-sm" alt="Preview">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                        <span class="text-white text-[10px] font-bold uppercase tracking-wider">Current Image</span>
                                    </div>
                                </div>
                                ` : `
                                <div class="mb-3 w-full h-40 bg-slate-50 border border-dashed border-slate-200 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-8 h-8 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                                    <span class="text-[10px] font-medium uppercase tracking-tight">No image selected</span>
                                </div>
                                `}
                                <div class="flex items-center gap-2">
                                    <input type="text" id="img_${index}" value="${escapeHtml(block.image || '')}" onchange="updateBlockField(${index}, 'image', this.value)" 
                                           class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-md text-sm font-mono" placeholder="/path/to/img.webp">
                                    <label class="px-3 py-2 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-md text-[10px] font-bold cursor-pointer hover:bg-indigo-100 transition-colors whitespace-nowrap">
                                        <input type="file" class="hidden" accept="image/*" onchange="startCropping(${index}, 'image', this)">
                                        Crop & Upload
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Bullet Points (One per line)</label>
                                <textarea onchange="updateBlockBullets(${index}, this.value)" rows="3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm" placeholder="FSC certified\nRemovable cushion">${(block.bullets || []).join('\n')}</textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="rev_${index}" ${block.reverse ? 'checked' : ''} onchange="updateBlockField(${index}, 'reverse', this.checked)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="rev_${index}" class="text-xs font-medium text-slate-600">Reverse Layout (Image on Left)</label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (block.type === 'hero_banner') {
                typeLabel = 'Hero Banner';
                fieldsHtml = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Large Title</label>
                                <input type="text" value="${escapeHtml(block.title || '')}" onchange="updateBlockField(${index}, 'title', this.value)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Description</label>
                                <textarea onchange="updateBlockField(${index}, 'text', this.value)" rows="3" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-md text-sm">${escapeHtml(block.text || '')}</textarea>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Banner Image Selection</label>
                            ${block.image ? `
                            <div class="mb-3 relative group">
                                <img src="${escapeHtml(block.image)}" class="w-full h-32 object-cover rounded-lg border border-slate-200 shadow-sm" alt="Banner Preview">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                    <span class="text-white text-[10px] font-bold uppercase tracking-wider">Banner Image</span>
                                </div>
                            </div>
                            ` : `
                            <div class="mb-3 p-4 bg-slate-900 rounded-lg text-center h-32 flex flex-col items-center justify-center">
                                <span class="text-[10px] font-black text-white/10 tracking-[10px] uppercase mb-2">MODERN</span>
                                <span class="text-white/40 text-[9px] uppercase font-bold tracking-widest">No Banner Selected</span>
                            </div>
                            `}
                            <div class="flex items-center gap-2">
                                <input type="text" id="img_${index}" value="${escapeHtml(block.image || '')}" onchange="updateBlockField(${index}, 'image', this.value)" 
                                       class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-md text-sm font-mono">
                                <label class="px-3 py-2 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-md text-[10px] font-bold cursor-pointer hover:bg-indigo-100 transition-colors whitespace-nowrap">
                                    <input type="file" class="hidden" accept="image/*" onchange="startCropping(${index}, 'image', this)">
                                    Crop & Upload
                                </label>
                            </div>
                        </div>
                    </div>
                `;
            } else if (block.type === 'icon_grid') {
                typeLabel = 'Icon Grid (4 Items)';
                fieldsHtml = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        ${[0,1,2,3].map(i => {
                            const item = (block.items || [])[i] || {title:'', text:'', image:''};
                            return `
                            <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg space-y-2">
                                ${item.image ? `
                                    <img src="${escapeHtml(item.image)}" alt="" class="w-full h-20 object-cover rounded-lg border border-slate-200 shadow-sm mb-1">
                                ` : `
                                    <div class="w-full h-20 bg-slate-100/50 border border-dashed border-slate-200 rounded-lg flex items-center justify-center mb-1">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></svg>
                                    </div>
                                `}
                                <div class="flex items-center gap-1">
                                    <label class="flex-1 p-1.5 px-3 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded text-[10px] font-bold cursor-pointer hover:bg-indigo-100 transition-colors text-center">
                                        <input type="file" class="hidden" accept="image/*" onchange="startCropping(${index}, 'image', this, ${i})">
                                        ${item.image ? 'Replace Icon' : '+ Upload Icon'}
                                    </label>
                                </div>
                                <input type="text" value="${escapeHtml(item.title || '')}" placeholder="Title" oninput="updateBlockGridItem(${index}, ${i}, 'title', this.value)" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-xs font-bold">
                                <textarea placeholder="Text" oninput="updateBlockGridItem(${index}, ${i}, 'text', this.value)" class="w-full px-2 py-1 bg-white border border-slate-200 rounded text-[10px]" rows="2">${escapeHtml(item.text || '')}</textarea>
                            </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }

            blockEl.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-[10px] font-black">${index + 1}</span>
                        <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">${typeLabel}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="moveBlock(${index}, -1)" class="p-1 px-2 text-slate-400 hover:text-indigo-600 transition-colors" title="Move Up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </button>
                        <button type="button" onclick="moveBlock(${index}, 1)" class="p-1 px-2 text-slate-400 hover:text-indigo-600 transition-colors" title="Move Down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </button>
                        <button type="button" onclick="removeBlock(${index})" class="p-1 px-2 text-slate-300 hover:text-rose-500 transition-colors ml-2" title="Remove Block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>
                ${fieldsHtml}
            `;
            blocksContainer.appendChild(blockEl);
        });

        detailsHidden.value = JSON.stringify(blocksData);
    }

    window.addBlock = function(type) {
        const newBlock = { type: type };
        if (type === 'feature_split') {
            newBlock.label = ''; newBlock.title = ''; newBlock.text = ''; newBlock.image = ''; newBlock.bullets = []; newBlock.reverse = false;
        } else if (type === 'hero_banner') {
            newBlock.title = ''; newBlock.text = ''; newBlock.image = '';
        } else if (type === 'icon_grid') {
            newBlock.items = [
                {title: '', text: '', image: ''},
                {title: '', text: '', image: ''},
                {title: '', text: '', image: ''},
                {title: '', text: '', image: ''}
            ];
        }
        blocksData.push(newBlock);
        renderBlocks();
    };

    window.removeBlock = function(index) {
        showConfirm({
            title: 'Remove Content Block',
            message: 'Are you sure you want to remove this content block? You will need to save the product to persist this change.',
            confirmText: 'Yes, Remove',
            type: 'danger',
            onConfirm: () => {
                blocksData.splice(index, 1);
                renderBlocks();
            }
        });
    };

    window.moveBlock = function(index, direction) {
        const newIndex = index + direction;
        if (newIndex >= 0 && newIndex < blocksData.length) {
            const temp = blocksData[index];
            blocksData[index] = blocksData[newIndex];
            blocksData[newIndex] = temp;
            renderBlocks();
        }
    };

    window.updateBlockField = function(index, field, value) {
        blocksData[index][field] = value;
        detailsHidden.value = JSON.stringify(blocksData);
    };

    window.updateBlockBullets = function(index, value) {
        blocksData[index].bullets = value.split('\n').map(b => b.trim()).filter(b => b !== '');
        detailsHidden.value = JSON.stringify(blocksData);
    };

    window.updateBlockGridItem = function(blockIndex, itemIndex, field, value) {
        if (!blocksData[blockIndex].items) blocksData[blockIndex].items = [];
        if (!blocksData[blockIndex].items[itemIndex]) blocksData[blockIndex].items[itemIndex] = {title:'', text:'', image:''};
        blocksData[blockIndex].items[itemIndex][field] = value;
        detailsHidden.value = JSON.stringify(blocksData);
    };

    // --- Additional Info Logic ---
    const additionalInfoContainer = document.getElementById('additional-info-container');
    const additionalInfoHidden = document.getElementById('additional_info_hidden');
    
    // Initial data from server
    let additionalInfoData = @json($product->additional_info ?? []);
    if (!Array.isArray(additionalInfoData)) {
        additionalInfoData = [];
    }

    function renderInfoRows() {
        additionalInfoContainer.innerHTML = '';
        if (additionalInfoData.length === 0) {
            additionalInfoContainer.innerHTML = `<div class="text-center py-4 border border-dashed border-slate-200 rounded-lg text-slate-400 text-xs mt-2">No specifications added yet</div>`;
        } else {
            additionalInfoData.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-3 group';
                row.innerHTML = `
                    <input type="text" value="${escapeHtml(item.label)}" onchange="updateInfoItem(${index}, 'label', this.value)" 
                           class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" placeholder="Label">
                    <input type="text" value="${escapeHtml(item.value)}" onchange="updateInfoItem(${index}, 'value', this.value)" 
                           class="flex-[2] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" placeholder="Value">
                    <button type="button" onclick="removeInfoRow(${index})" class="p-2 text-slate-300 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </button>
                `;
                additionalInfoContainer.appendChild(row);
            });
        }
        additionalInfoHidden.value = JSON.stringify(additionalInfoData);
    }

    window.addInfoRow = function() {
        additionalInfoData.push({ label: '', value: '' });
        renderInfoRows();
    };

    window.removeInfoRow = function(index) {
        additionalInfoData.splice(index, 1);
        renderInfoRows();
    };

    window.updateInfoItem = function(index, key, val) {
        additionalInfoData[index][key] = val;
        additionalInfoHidden.value = JSON.stringify(additionalInfoData);
    };

    // Initialize all logic
    renderBlocks();
    renderInfoRows();
    loadVariants();

    // Add variant button
    document.getElementById('add-variant-btn').addEventListener('click', openAddVariantModal);

    // Handle tags
    document.getElementById('product-form').addEventListener('submit', function(e) {
        const tagsInput = document.getElementById('tags_input').value;
        const tagsArray = tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag !== '');
        const formattedTags = tagsArray.map(tag => ({ text: tag }));
        document.getElementById('tags_hidden').value = JSON.stringify(formattedTags);
    });

    // Handle image selection display
    const productImagesInput = document.getElementById('product-images');
    const productImagesSelected = document.getElementById('product-images-selected');

    if (productImagesInput && productImagesSelected) {
        productImagesInput.addEventListener('change', function() {
            const files = Array.from(this.files || []);
            productImagesSelected.textContent = files.length
                ? `${files.length} image${files.length > 1 ? 's' : ''} selected. Saving will add them to the current gallery.`
                : '';
        });
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    // --- Cropping Logic ---
    let cropperInstance = null;
    let currentCroppingContext = null;

    const cropModal = document.getElementById('crop-modal');
    const cropImageEl = document.getElementById('crop-image-element');
    const saveCropBtn = document.getElementById('save-crop-btn');
    const cropSpinner = document.getElementById('crop-btn-spinner');

    // Move crop modal to body to escape any stacking context trap
    if (cropModal.parentElement !== document.body) {
        document.body.appendChild(cropModal);
    }

    window.startCropping = function(contextOrBlockIndex, fieldOrIndex, input, itemIndex = null) {
        const file = input.files[0];
        if (!file) return;

        currentCroppingContext = { context: contextOrBlockIndex, field: fieldOrIndex, itemIndex: itemIndex, input: input };
        
        const reader = new FileReader();
        reader.onload = function(e) {
            cropImageEl.src = e.target.result;
            cropModal.classList.remove('hidden');
            cropModal.classList.add('flex');
            
            if (cropperInstance) cropperInstance.destroy();
            
            // Set aspect ratio based on block type if needed
            let aspectRatio = NaN; // default free crop
            
            if (typeof contextOrBlockIndex === 'number') {
                const block = blocksData[contextOrBlockIndex];
                if (block.type === 'feature_split') {
                    aspectRatio = 4/3; // Default 800x600 ratio
                } else if (block.type === 'hero_banner') {
                    aspectRatio = 16/9; // Wider for banners
                } else if (block.type === 'icon_grid') {
                    aspectRatio = 1; // Square for icons
                }
            } else if (contextOrBlockIndex === 'gallery_add' || contextOrBlockIndex === 'gallery_replace') {
                aspectRatio = 1; // Standardize square images for gallery
            }

            cropperInstance = new Cropper(cropImageEl, {
                aspectRatio: aspectRatio,
                viewMode: 1, // Set to 1 to ensure crop box doesn't exceed canvas
                dragMode: 'move',
                autoCropArea: 1, // Start with a 100% crop area relative to the canvas
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };
        reader.readAsDataURL(file);
    };

    window.closeCropModal = function() {
        cropModal.classList.add('hidden');
        cropModal.classList.remove('flex');
        if (cropperInstance) cropperInstance.destroy();
    };

    saveCropBtn.onclick = function() {
        if (!cropperInstance) return;

        cropSpinner.classList.remove('hidden');
        saveCropBtn.disabled = true;

        cropperInstance.getCroppedCanvas().toBlob((blob) => {
            const formData = new FormData();
            formData.append('image', blob, 'cropped.webp');
            formData.append('_token', '{{ csrf_token() }}');

            const ctx = currentCroppingContext;

            // variant_color: feed the cropped blob back into the file input for form submit
            if (ctx.context === 'variant_color') {
                const croppedFile = new File([blob], 'cropped.webp', { type: 'image/webp' });
                const dt = new DataTransfer();
                dt.items.add(croppedFile);
                const colorInput = document.getElementById('color-image-input');
                colorInput.files = dt.files;

                // Update preview
                const previewImg = document.getElementById('color-image-preview-img');
                const previewWrap = document.getElementById('color-image-preview');
                if (previewImg && previewWrap) {
                    previewImg.src = URL.createObjectURL(blob);
                    previewWrap.classList.remove('hidden');
                }
                const colorBtnText = document.getElementById('color-image-btn-text');
                if (colorBtnText) colorBtnText.textContent = '✓ Image Selected';

                cropSpinner.classList.add('hidden');
                saveCropBtn.disabled = false;
                closeCropModal();
                return;
            }

            // For rich content blocks, identify the old image to delete it on the server
            if (typeof ctx.context === 'number') {
                let oldPath = '';
                const block = blocksData[ctx.context];
                if (ctx.itemIndex !== null) {
                    // Icon Grid item
                    if (block.items && block.items[ctx.itemIndex]) {
                        oldPath = block.items[ctx.itemIndex][ctx.field || 'image'];
                    }
                } else {
                    // Feature Split or Hero Banner
                    oldPath = block[ctx.field || 'image'];
                }
                
                if (oldPath) {
                    formData.append('old_path', oldPath);
                }
            }

            let url = `/admin/products/{{ $product->id }}/upload-block-image`;

            if (ctx.context === 'gallery_add') {
                url = `/admin/products/{{ $product->id }}/upload-gallery-image`;
            } else if (ctx.context === 'gallery_replace') {
                url = `/admin/products/{{ $product->id }}/replace-gallery-image/${ctx.field}`;
            }

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || `Server returned ${res.status}`);
                }
                return data;
            })
            .then(res => {
                if (ctx.context === 'gallery_add' || ctx.context === 'gallery_replace') {
                    if (res.status) {
                        showToast('Gallery updated successfully', 'success');
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        showToast(res.message || 'Upload failed', 'error');
                    }
                    return;
                }

                if (res.status) {
                    if (ctx.itemIndex !== null) {
                        updateBlockGridItem(ctx.context, ctx.itemIndex, ctx.field, res.data.url);
                    } else {
                        updateBlockField(ctx.context, ctx.field, res.data.url);
                    }
                    renderBlocks();
                    closeCropModal();
                    showToast('Image uploaded successfully', 'success');
                } else {
                    showToast(res.message || 'Upload failed', 'error');
                }
            })
            .catch(err => {
                console.error('Upload error:', err);
                showToast(`Upload error: ${err.message}`, 'error');
            })
            .finally(() => {
                cropSpinner.classList.add('hidden');
                saveCropBtn.disabled = false;
            });
        }, 'image/webp', 0.9);
    };

    window.deleteGalleryImage = function(url) {
        showConfirm({
            title: 'Delete Image',
            message: 'Are you sure you want to delete this image from the gallery? This action is permanent.',
            confirmText: 'Yes, Delete',
            type: 'danger',
            onConfirm: () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    };
</script>

@endsection
