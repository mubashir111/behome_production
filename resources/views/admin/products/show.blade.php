@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
<div class="pb-10">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Product: {{ $product->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage product details, pricing, and inventory.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn-primary">Edit Product</a>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">Back to Products</a>
        </div>
    </div>

    @include('admin._alerts')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Gallery & Description -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Gallery -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Product Gallery</h2>
                    <p class="admin-card-subtitle">{{ $product->getMedia('product')->count() }} image(s)</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    @if($product->getMedia('product')->count() > 0)
                        <div class="aspect-video rounded-lg overflow-hidden border border-slate-200">
                            @php $firstMedia = $product->getMedia('product')->first(); @endphp
                            <img src="{{ $firstMedia ? ($firstMedia->hasGeneratedConversion('cover') ? $firstMedia->getUrl('cover') : $firstMedia->getUrl()) : '' }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </div>
                        @if($product->getMedia('product')->count() > 1)
                        <div class="grid grid-cols-4 gap-3">
                            @foreach($product->getMedia('product')->slice(1) as $media)
                                <div class="aspect-square rounded-lg overflow-hidden border border-slate-200 hover:border-indigo-300 transition-colors cursor-pointer">
                                    <img src="{{ $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl() }}" alt="" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="aspect-video rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="font-semibold">No images uploaded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Description -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Description</h2>
                </div>
                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">
                    {{ $product->description ?: 'No description provided.' }}
                </div>
            </div>
        </div>

        <!-- Right Column: Status, Pricing & Inventory -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Status</h2>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <span class="text-slate-600 text-sm font-medium">Product Status</span>
                        @if($product->status == 5)
                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">Active</span>
                        @else
                            <span class="inline-block px-3 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded">Inactive</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <span class="text-slate-600 text-sm font-medium">Purchasable</span>
                        <span class="font-semibold text-slate-900">{{ $product->can_purchasable ? '✓ Yes' : '✗ No' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <span class="text-slate-600 text-sm font-medium">Refundable</span>
                        <span class="font-semibold text-slate-900">{{ $product->refundable == 5 ? '✓ Yes' : '✗ No' }}</span>
                    </div>
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Pricing</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Selling Price</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-1">${{ number_format($product->selling_price, 2) }}</p>
                    </div>
                    <hr class="border-slate-200">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold uppercase">Cost Price</p>
                            <p class="font-semibold text-slate-900 mt-1">${{ number_format($product->buying_price, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-semibold uppercase">Profit</p>
                            @php $profit = $product->selling_price - $product->buying_price; @endphp
                            <p class="font-semibold text-emerald-600 mt-1">+${{ number_format($profit, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Inventory</h2>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between p-2 hover:bg-slate-50 rounded">
                        <span class="text-slate-600">SKU</span>
                        <span class="font-mono font-semibold text-slate-900">{{ $product->sku }}</span>
                    </div>
                    <div class="flex justify-between p-2 hover:bg-slate-50 rounded">
                        <span class="text-slate-600">Category</span>
                        <span class="font-semibold text-slate-900">{{ $product->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between p-2 hover:bg-slate-50 rounded">
                        <span class="text-slate-600">Brand</span>
                        <span class="font-semibold text-slate-900">{{ $product->brand->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between p-2 hover:bg-slate-50 rounded">
                        <span class="text-slate-600">Unit</span>
                        <span class="font-semibold text-slate-900">{{ $product->unit->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between p-2 hover:bg-slate-50 rounded">
                        <span class="text-slate-600">Weight</span>
                        <span class="font-semibold text-slate-900">{{ $product->weight ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
