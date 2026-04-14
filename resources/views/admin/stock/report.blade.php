@extends('layouts.admin')

@section('title', 'Stock Report')

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Stock Report</h2>
            <p class="admin-page-subtitle">Live inventory levels across all products.</p>
        </div>
        <a href="{{ route('admin.purchases.create') }}" class="admin-btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Stock (Purchase)
        </a>
    </div>

    @include('admin._alerts')

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px;">
        <div class="admin-card" style="padding:20px 22px;border-left:4px solid #6366f1;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 6px;">Total Products</p>
            <p style="font-size:28px;font-weight:800;color:#0f172a;margin:0;">{{ $totalProducts }}</p>
        </div>
        <a href="?filter=in_stock" style="text-decoration:none;">
            <div class="admin-card" style="padding:20px 22px;border-left:4px solid #22c55e;cursor:pointer;transition:.15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(34,197,94,.15)'" onmouseout="this.style.boxShadow=''">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 6px;">In Stock</p>
                <p style="font-size:28px;font-weight:800;color:#16a34a;margin:0;">{{ $inStock }}</p>
            </div>
        </a>
        <a href="?filter=low_stock" style="text-decoration:none;">
            <div class="admin-card" style="padding:20px 22px;border-left:4px solid #f59e0b;cursor:pointer;transition:.15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(245,158,11,.15)'" onmouseout="this.style.boxShadow=''">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 6px;">Low Stock</p>
                <p style="font-size:28px;font-weight:800;color:#d97706;margin:0;">{{ $lowStock }}</p>
            </div>
        </a>
        <a href="?filter=out_of_stock" style="text-decoration:none;">
            <div class="admin-card" style="padding:20px 22px;border-left:4px solid #ef4444;cursor:pointer;transition:.15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(239,68,68,.15)'" onmouseout="this.style.boxShadow=''">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 6px;">Out of Stock</p>
                <p style="font-size:28px;font-weight:800;color:#dc2626;margin:0;">{{ $outOfStock }}</p>
            </div>
        </a>
        <div class="admin-card" style="padding:20px 22px;border-left:4px solid #8b5cf6;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 6px;">Stock Value</p>
            <p style="font-size:22px;font-weight:800;color:#7c3aed;margin:0;">{{ config('app.currency_symbol', '$') }}{{ number_format($totalStockValue, 2) }}</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="admin-card" style="padding:16px 20px;margin-bottom:20px;">
        <form method="GET" action="{{ route('admin.stock.report') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search product name or SKU…"
                   style="flex:1;min-width:200px;padding:9px 14px;border-radius:10px;border:2px solid #e2e8f0;font-size:13px;outline:none;color:#0f172a;"
                   onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e2e8f0'">

            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                @foreach(['all'=>'All','in_stock'=>'In Stock','low_stock'=>'Low Stock','out_of_stock'=>'Out of Stock'] as $val=>$label)
                    <a href="?filter={{ $val }}{{ $search ? '&search='.$search : '' }}"
                       style="padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:.15s;
                              {{ $filter===$val ? 'background:#6366f1;color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <button type="submit" style="padding:9px 18px;border-radius:10px;background:#6366f1;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.stock.report') }}?filter={{ $filter }}"
                   style="padding:9px 14px;border-radius:10px;background:#f1f5f9;color:#64748b;font-size:13px;font-weight:600;text-decoration:none;">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Products Table --}}
    <div class="admin-card" style="padding:0;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:13px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;white-space:nowrap;">Product</th>
                        <th style="padding:13px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">SKU</th>
                        <th style="padding:13px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Category</th>
                        <th style="padding:13px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Available Qty</th>
                        <th style="padding:13px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Low Stock At</th>
                        <th style="padding:13px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Status</th>
                        <th style="padding:13px 18px;text-align:right;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $qty = (int)($product->stock_qty ?? 0);
                            $low = (int)($product->low_stock_quantity_warning ?? 5);
                            if ($qty <= 0) {
                                $statusColor = '#dc2626'; $statusBg = '#fef2f2'; $statusLabel = 'Out of Stock';
                            } elseif ($qty <= $low) {
                                $statusColor = '#d97706'; $statusBg = '#fffbeb'; $statusLabel = 'Low Stock';
                            } else {
                                $statusColor = '#16a34a'; $statusBg = '#f0fdf4'; $statusLabel = 'In Stock';
                            }
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                            <td style="padding:14px 18px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:42px;height:42px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f1f5f9;">
                                        @if($product->thumb)
                                            <img src="{{ $product->thumb }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                                        @else
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p style="font-weight:600;color:#0f172a;margin:0;font-size:13px;">{{ Str::limit($product->name, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 18px;color:#64748b;font-size:12px;font-family:monospace;">{{ $product->sku ?: '—' }}</td>
                            <td style="padding:14px 18px;color:#64748b;font-size:12px;">{{ $product->category->name ?? '—' }}</td>
                            <td style="padding:14px 18px;text-align:center;">
                                <span style="font-size:20px;font-weight:800;color:{{ $statusColor }};">{{ $qty }}</span>
                                @if($qty < 0)
                                    <div style="font-size:10px;color:#ef4444;font-weight:600;">⚠ Oversold</div>
                                @endif
                            </td>
                            <td style="padding:14px 18px;text-align:center;color:#94a3b8;font-size:13px;">{{ $low }}</td>
                            <td style="padding:14px 18px;text-align:center;">
                                <span style="padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:{{ $statusBg }};color:{{ $statusColor }};">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td style="padding:14px 18px;text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    <a href="{{ route('admin.stock.product.history', $product->id) }}"
                                       style="padding:6px 12px;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;"
                                       onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                        History
                                    </a>
                                    <a href="{{ route('admin.purchases.create') }}"
                                       style="padding:6px 12px;border-radius:8px;background:#ede9fe;color:#7c3aed;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;"
                                       onmouseover="this.style.background='#ddd6fe'" onmouseout="this.style.background='#ede9fe'">
                                        + Stock
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:48px;text-align:center;color:#94a3b8;">
                                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- Recent Movements --}}
    <div class="admin-card" style="margin-top:24px;">
        <h3 class="admin-section-title" style="margin-bottom:16px;">
            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            Recent Stock Movements
        </h3>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Product</th>
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Type</th>
                        <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Qty Change</th>
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMovements as $move)
                        @php
                            $type = class_basename($move->model_type ?? '');
                            $isIn = $move->quantity > 0;
                        @endphp
                        <tr style="border-bottom:1px solid #f8fafc;">
                            <td style="padding:10px 16px;color:#0f172a;font-weight:500;">{{ Str::limit($move->product?->name ?? '—', 40) }}</td>
                            <td style="padding:10px 16px;">
                                <span style="padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;
                                    {{ $type === 'Purchase' ? 'background:#f0fdf4;color:#16a34a;' : ($type === 'Order' ? 'background:#fef2f2;color:#dc2626;' : 'background:#f1f5f9;color:#64748b;') }}">
                                    {{ $type }}
                                </span>
                            </td>
                            <td style="padding:10px 16px;text-align:center;font-weight:700;font-size:14px;color:{{ $isIn ? '#16a34a' : '#dc2626' }};">
                                {{ $isIn ? '+' : '' }}{{ $move->quantity }}
                            </td>
                            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">{{ $move->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
