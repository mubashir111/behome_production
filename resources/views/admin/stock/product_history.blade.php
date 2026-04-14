@extends('layouts.admin')

@section('title', 'Stock History — ' . $product->name)

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Stock History</h2>
            <p class="admin-page-subtitle">{{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.stock.report') }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;background:#f1f5f9;color:#64748b;font-size:13px;font-weight:600;text-decoration:none;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Stock Report
        </a>
    </div>

    {{-- Product Info + Current Stock --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
        <div class="admin-card" style="padding:20px;grid-column:span 2;display:flex;gap:16px;align-items:center;">
            <div style="width:64px;height:64px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#f1f5f9;">
                @if($product->thumb)
                    <img src="{{ $product->thumb }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                @endif
            </div>
            <div>
                <p style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 4px;">{{ $product->name }}</p>
                <p style="font-size:12px;color:#94a3b8;margin:0;">SKU: <span style="font-family:monospace;color:#64748b;">{{ $product->sku ?: '—' }}</span></p>
                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0;">Buying Price: <strong style="color:#0f172a;">{{ config('app.currency_symbol','$') }}{{ number_format($product->buying_price, 2) }}</strong></p>
            </div>
        </div>

        @php
            $qty = $currentStock;
            $low = (int)($product->low_stock_quantity_warning ?? 5);
            if ($qty <= 0) { $sc = '#dc2626'; $sb = '#fef2f2'; $sl = 'Out of Stock'; }
            elseif ($qty <= $low) { $sc = '#d97706'; $sb = '#fffbeb'; $sl = 'Low Stock'; }
            else { $sc = '#16a34a'; $sb = '#f0fdf4'; $sl = 'In Stock'; }
        @endphp
        <div class="admin-card" style="padding:20px;text-align:center;background:{{ $sb }};border:2px solid {{ $sc }}20;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 8px;">Current Stock</p>
            <p style="font-size:40px;font-weight:900;color:{{ $sc }};margin:0;line-height:1;">{{ $qty }}</p>
            <p style="font-size:12px;font-weight:700;color:{{ $sc }};margin:6px 0 0;">{{ $sl }}</p>
            @if($qty < 0)
                <p style="font-size:11px;color:#ef4444;margin:4px 0 0;font-weight:600;">⚠ Oversold — add purchase</p>
            @endif
        </div>
    </div>

    {{-- Movement History --}}
    <div class="admin-card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">All Stock Movements</h3>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:12px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Date</th>
                        <th style="padding:12px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Source</th>
                        <th style="padding:12px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Ref #</th>
                        <th style="padding:12px 18px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Variation</th>
                        <th style="padding:12px 18px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Qty Change</th>
                        <th style="padding:12px 18px;text-align:right;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    @php $running = 0; $rows = $movements->items(); @endphp
                    @forelse($rows as $move)
                        @php
                            $type = class_basename($move->model_type ?? '');
                            $isIn = $move->quantity > 0;
                            $running += $move->quantity;
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                            <td style="padding:13px 18px;color:#64748b;font-size:12px;white-space:nowrap;">{{ $move->created_at?->format('M d, Y H:i') }}</td>
                            <td style="padding:13px 18px;">
                                <span style="padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;
                                    {{ $type==='Purchase' ? 'background:#f0fdf4;color:#16a34a;' : ($type==='Order' ? 'background:#fef2f2;color:#dc2626;' : ($type==='ReturnOrder' ? 'background:#eff6ff;color:#2563eb;' : ($type==='Damage' ? 'background:#fff7ed;color:#c2410c;' : 'background:#f1f5f9;color:#64748b;'))) }}">
                                    {{ $type ?: '—' }}
                                </span>
                            </td>
                            <td style="padding:13px 18px;color:#6366f1;font-size:12px;font-family:monospace;">#{{ $move->model_id }}</td>
                            <td style="padding:13px 18px;color:#64748b;font-size:12px;">{{ $move->variation_names ?: '—' }}</td>
                            <td style="padding:13px 18px;text-align:center;">
                                <span style="font-size:16px;font-weight:800;color:{{ $isIn ? '#16a34a' : '#dc2626' }};">
                                    {{ $isIn ? '+' : '' }}{{ $move->quantity }}
                                </span>
                            </td>
                            <td style="padding:13px 18px;text-align:right;color:#0f172a;font-weight:600;">
                                {{ config('app.currency_symbol','$') }}{{ number_format($move->price, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px;text-align:center;color:#94a3b8;">No stock movements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
