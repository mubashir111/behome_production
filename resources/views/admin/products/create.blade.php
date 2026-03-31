@extends('layouts.admin')

@section('content')
<div class="pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900 mb-2">
                Add New Product
            </h1>
            <p class="text-slate-500 text-lg">Create a new item for your store inventory.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" 
           class="glass px-6 py-3 rounded-2xl flex items-center gap-2 text-slate-600 hover:text-indigo-600 transition-all duration-300 focus-visible:ring-2 focus-visible:ring-indigo-500 outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to List
        </a>
    </div>

    <!-- Form Container -->
    <div class="admin-card">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Basic Information Section -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold font-outfit text-slate-900 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm font-bold">01</span>
                    Basic Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-bold text-slate-700 ml-1">Product Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-300 bg-white text-slate-900"
                               placeholder="e.g. Modern Leather Sofa">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="sku" class="block text-sm font-bold text-slate-700 ml-1">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                               class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-300 bg-white text-slate-900"
                               placeholder="Unique SKU code">
                        @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="product_category_id" class="block text-sm font-bold text-slate-700 ml-1">Category</label>
                        <select name="product_category_id" id="product_category_id" required
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-300 appearance-none bg-white text-slate-900">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('product_category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="product_brand_id" class="block text-sm font-bold text-slate-700 ml-1">Brand (Optional)</label>
                        <select name="product_brand_id" id="product_brand_id"
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-300 appearance-none bg-white text-slate-900">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('product_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Pricing & Inventory Section -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold font-outfit text-slate-900 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 text-sm font-bold">02</span>
                    Pricing & Inventory
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="buying_price" class="block text-sm font-bold text-slate-700 ml-1">Buying Price</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" name="buying_price" id="buying_price" value="{{ old('buying_price') }}" required
                                   class="w-full px-10 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 bg-white text-slate-900">
                        </div>
                        @error('buying_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="selling_price" class="block text-sm font-bold text-slate-700 ml-1">Selling Price</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                            <input type="number" step="0.01" name="selling_price" id="selling_price" value="{{ old('selling_price') }}" required
                                   class="w-full px-10 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 bg-white text-slate-900">
                        </div>
                        @error('selling_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label for="tax_id" class="block text-sm font-bold text-slate-700 ml-1">Tax</label>
                        <select name="tax_id[]" id="tax_id" multiple
                                class="w-full px-5 py-3 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 min-h-[100px] bg-white text-slate-900">
                            @foreach($taxes as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->tax_rate }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="unit_id" class="block text-sm font-bold text-slate-700 ml-1">Unit</label>
                        <select name="unit_id" id="unit_id" required
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 appearance-none bg-white text-slate-900">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="weight" class="block text-sm font-bold text-slate-700 ml-1">Weight</label>
                        <input type="text" name="weight" id="weight" value="{{ old('weight') }}"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 bg-white text-slate-900"
                               placeholder="e.g. 1.5kg">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label for="barcode_id" class="block text-sm font-bold text-slate-700 ml-1">Barcode Type</label>
                        <select name="barcode_id" id="barcode_id" required
                                class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 appearance-none bg-white text-slate-900">
                            @foreach($barcodes as $barcode)
                                <option value="{{ $barcode->id }}" {{ old('barcode_id') == $barcode->id ? 'selected' : '' }}>{{ $barcode->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="maximum_purchase_quantity" class="block text-sm font-bold text-slate-700 ml-1">Max Purchase Qty</label>
                        <input type="number" name="maximum_purchase_quantity" id="maximum_purchase_quantity" value="{{ old('maximum_purchase_quantity', 10) }}"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 bg-white text-slate-900">
                    </div>

                    <div class="space-y-2">
                        <label for="low_stock_quantity_warning" class="block text-sm font-bold text-slate-700 ml-1">Low Stock Warning</label>
                        <input type="number" name="low_stock_quantity_warning" id="low_stock_quantity_warning" value="{{ old('low_stock_quantity_warning', 2) }}"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition-all duration-300 bg-white text-slate-900">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            @include('admin.products._offer_card')

            <hr class="border-slate-100">

            <!-- Description & Tags Section -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold font-outfit text-slate-900 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600 text-sm font-bold">03</span>
                    Description & Details
                </h2>

                <div class="space-y-2">
                    <label for="description" class="block text-sm font-bold text-slate-700 ml-1">Description</label>
                    <textarea name="description" id="description" rows="5"
                              class="w-full px-5 py-4 rounded-3xl border border-slate-200 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition-all duration-300 bg-white text-slate-900"
                              placeholder="Describe your product...">{{ old('description') }}</textarea>
                </div>

                <div class="space-y-2">
                    <label for="tags" class="block text-sm font-bold text-slate-700 ml-1">Tags (Comma separated)</label>
                    <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                           class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 outline-none transition-all duration-300 bg-white text-slate-900"
                           placeholder="e.g. sofa, modern, leather">
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Media Section -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold font-outfit text-slate-900 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-sm font-bold">04</span>
                    Product Media
                </h2>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700 ml-1">Product Images</label>
                    <div class="relative group">
                        <input type="file" name="images[]" id="images" multiple
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 transition-all focus-visible:ring-4 focus-visible:ring-amber-500/20 outline-none" 
                               aria-label="Upload Images">
                        <div class="border-2 border-dashed border-slate-200 group-hover:border-amber-500/40 rounded-3xl p-10 flex flex-col items-center justify-center transition-all duration-300 bg-slate-50">
                            <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-4 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-slate-600 font-bold">Click to upload or drag and drop</p>
                            <p class="text-slate-400 text-sm mt-1">PNG, JPG or WebP (Max. 2MB Each)</p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Settings Section -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold font-outfit text-slate-900 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm font-bold">05</span>
                    Status & Visibility
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center justify-between bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-colors">
                        <div>
                            <label for="can_purchasable" class="text-slate-900 font-bold cursor-pointer">Available for Purchase</label>
                            <p class="text-slate-500 text-sm">Customers can buy this item</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="can_purchasable" value="10">
                            <input type="checkbox" name="can_purchasable" id="can_purchasable" value="5" checked class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-colors">
                        <div>
                            <label for="show_stock_out" class="text-slate-900 font-bold cursor-pointer">Show Out of Stock</label>
                            <p class="text-slate-500 text-sm">Show even if out of stock</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="show_stock_out" value="10">
                            <input type="checkbox" name="show_stock_out" id="show_stock_out" value="5" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-colors">
                        <div>
                            <label for="refundable" class="text-slate-900 font-bold cursor-pointer">Refundable</label>
                            <p class="text-slate-500 text-sm">Is this item refundable?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="refundable" value="10">
                            <input type="checkbox" name="refundable" id="refundable" value="5" class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:bg-slate-100 transition-colors">
                        <div>
                            <label for="status" class="text-slate-900 font-bold cursor-pointer">Product Status</label>
                            <p class="text-slate-500 text-sm">Active or Inactive</p>
                        </div>
                        <select name="status" id="status" class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer transition-all">
                            <option value="5">Active</option>
                            <option value="10">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="admin-form-actions">
                <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">Discard</a>
                <button type="submit" class="admin-btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>
@endsection
