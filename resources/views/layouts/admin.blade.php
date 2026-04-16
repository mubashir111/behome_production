<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <style>
        :root {
            --sidebar-bg: #0f0f1a;
            --sidebar-border: rgba(255,255,255,0.06);
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active-from: #4f46e5;
            --sidebar-active-to: #7c3aed;
            --accent: #6366f1;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
        }

        .font-outfit { font-family: 'Outfit', sans-serif; }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 30;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .admin-sidebar::-webkit-scrollbar { display: none; }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .sidebar-logo-text span { color: #818cf8; }

        .sidebar-nav { flex: 1; padding: 12px 12px; }

        .sidebar-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            padding: 16px 12px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 1px;
        }
        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: rgba(255,255,255,0.9);
        }
        .sidebar-link svg { flex-shrink: 0; opacity: 0.7; }
        .sidebar-link:hover svg { opacity: 1; }

        .sidebar-link.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        }
        .sidebar-link.active svg { opacity: 1; }

        .sidebar-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            background: #ef4444;
            color: white;
            border-radius: 100px;
            padding: 1px 10px;
        }

        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
        }
        .sidebar-pro-card {
            background: linear-gradient(135deg, rgba(79,70,229,0.25) 0%, rgba(124,58,237,0.25) 100%);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 14px;
            padding: 14px;
        }
        .sidebar-pro-card p:first-child {
            font-size: 13px; font-weight: 700; color: #c7d2fe;
        }
        .sidebar-pro-card p:last-child {
            font-size: 11px; color: rgba(199,210,254,0.6); margin-top: 2px;
        }

        /* ── Topbar ── */
        .admin-topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .topbar-avatar {
            width: 38px; height: 38px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e0e7ff;
        }

        /* ── Main content ── */
        .admin-main { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; width: calc(100% - 260px); }
        .admin-content { flex: 1; padding: 24px 28px 40px; }
        /* Prevent Bootstrap/Tailwind row negative-margin from bleeding outside admin-content */
        .admin-content > .row { margin-left: 0; margin-right: 0; }
        .admin-content > .row > [class*="col-"] { padding-left: 12px; padding-right: 12px; }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px 22px 18px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s, box-shadow 0.18s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.08);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
        }
        .stat-card.revenue::before { background: linear-gradient(180deg,#10b981,#059669); }
        .stat-card.orders::before  { background: linear-gradient(180deg,#6366f1,#4f46e5); }
        .stat-card.customers::before { background: linear-gradient(180deg,#f59e0b,#d97706); }
        .stat-card.products::before { background: linear-gradient(180deg,#ec4899,#db2777); }
        .stat-card.reviews::before  { background: linear-gradient(180deg,#eab308,#ca8a04); }
        .stat-card.returns::before  { background: linear-gradient(180deg,#ef4444,#dc2626); }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .trend-up {
            display: inline-flex; align-items: center; gap: 3px;
            font-size: 11px; font-weight: 700;
            color: #10b981;
            background: #ecfdf5;
            border-radius: 100px;
            padding: 2px 8px;
        }

        /* ── Quick action cards ── */
        .quick-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            display: flex; align-items: center; gap: 14px;
            text-decoration: none;
            transition: all 0.18s;
        }
        .quick-card:hover {
            border-color: #6366f1;
            box-shadow: 0 8px 24px rgba(99,102,241,0.12);
            transform: translateY(-1px);
        }
        .quick-card-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .quick-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .quick-card-sub {
            font-size: 11.5px;
            color: #94a3b8;
        }

        /* ── Data tables ── */
        .data-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .data-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .data-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .data-card-link {
            font-size: 12.5px;
            font-weight: 600;
            color: #6366f1;
            text-decoration: none;
            padding: 5px 12px;
            background: #eef2ff;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .data-card-link:hover { background: #e0e7ff; }

        /* ── Status badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11.5px; font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
        }
        .badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .badge-pending  { color: #78350f; background: #fef3c7; border: 1px solid #fcd34d; font-weight: 600; } .badge-pending::before  { background: #d97706; }
        .badge-confirmed{ color: #3730a3; background: #e0e7ff; border: 1px solid #c7d2fe; font-weight: 600; } .badge-confirmed::before{ background: #4f46e5; }
        .badge-ongoing  { color: #1e3a8a; background: #bfdbfe; border: 1px solid #93c5fd; font-weight: 600; } .badge-ongoing::before  { background: #2563eb; }
        .badge-delivered{ color: #15803d; background: #d1fae5; border: 1px solid #a7f3d0; font-weight: 600; } .badge-delivered::before{ background: #059669; }
        .badge-cancelled{ color: #7f1d1d; background: #fee2e2; border: 1px solid #fecaca; font-weight: 600; } .badge-cancelled::before{ background: #dc2626; }
        .badge-unknown  { color: #1f2937; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 600; } .badge-unknown::before  { background: #6b7280; }

        /* ── Hero banner ── */
        .dash-hero {
            background: linear-gradient(135deg, #312e81 0%, #4f46e5 40%, #7c3aed 100%);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .dash-hero::after {
            content: '';
            position: absolute;
            right: -60px; top: -60px;
            width: 260px; height: 260px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .dash-hero::before {
            content: '';
            position: absolute;
            right: 80px; bottom: -80px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-main { margin-left: 0; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeInUp 0.4s ease both; }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.10s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.20s; }
        .delay-5 { animation-delay: 0.25s; }
        .delay-6 { animation-delay: 0.30s; }

        /* ═══ ADMIN COMPONENT DESIGN SYSTEM ═══ */
        .admin-page{display:flex;flex-direction:column;gap:24px}
        .admin-page-header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}
        .admin-page-title{font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin:0}
        .admin-page-subtitle{font-size:13.5px;color:#94a3b8;margin:3px 0 0}
        .admin-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:24px}
        .admin-section-title{font-family:'Outfit',sans-serif;font-size:15px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:10px;padding-bottom:14px;margin-bottom:18px;border-bottom:1px solid #f1f5f9}
        /* Tables */
        .admin-table-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden}
        .admin-table-wrap{overflow-x:auto}
        .admin-table{width:100%;border-collapse:collapse;font-size:13.5px}
        .admin-table-head tr{background:#f8fafc}
        .admin-table-head-cell{padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;border-bottom:1px solid #f1f5f9}
        .admin-table-body tr{border-top:1px solid #f1f5f9;transition:background .12s}
        .admin-table-body tr:hover,.admin-table-row:hover{background:#f8fafc}
        .admin-table-row{border-top:1px solid #f1f5f9;transition:background .12s}
        .admin-table-cell{padding:13px 16px;color:#334155;vertical-align:middle}
        .admin-table-actions{padding:10px 16px;text-align:right;white-space:nowrap;vertical-align:middle}
        .admin-table-actions a,.admin-table-actions button{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;transition:background .15s,color .15s;color:#94a3b8;background:transparent;text-decoration:none;vertical-align:middle}
        .admin-table-actions a:hover,.admin-table-actions button:hover{background:#f1f5f9;color:#334155}
        .admin-table-pagination{padding:14px 18px;border-top:1px solid #f1f5f9;background:#fafafa}
        /* Filter bar */
        .admin-filter-bar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 18px;margin-bottom:18px}
        /* Form */
        .admin-form-group{margin-bottom:20px}
        .admin-label{display:block;font-size:12.5px;font-weight:700;color:#374151;margin-bottom:6px}
        .admin-input,.admin-select,.admin-textarea{width:100%;padding:10px 14px;font-size:13.5px;color:#1e293b;background:#fff;border:1px solid #e2e8f0;border-radius:10px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:'Inter',sans-serif}
        .admin-input:focus,.admin-select:focus,.admin-textarea:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .admin-input::placeholder,.admin-textarea::placeholder{color:#cbd5e1}
        .admin-textarea{resize:vertical;min-height:100px}
        .admin-select{appearance:none;cursor:pointer}
        .admin-field-error{font-size:11.5px;color:#ef4444;margin-top:4px}
        .admin-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px}
        .admin-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px}
        @media(max-width:768px){.admin-grid-2,.admin-grid-3{grid-template-columns:1fr}}
        .admin-toggle-row{display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #f1f5f9;border-radius:12px;padding:14px 16px}
        .admin-toggle-label{font-size:13.5px;font-weight:700;color:#1e293b}
        .admin-toggle-sub{font-size:12px;color:#94a3b8;margin-top:2px}
        /* Buttons */
        .admin-btn-primary{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);color:#fff;font-size:13.5px;font-weight:700;border-radius:10px;border:none;cursor:pointer;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.3);transition:opacity .15s,box-shadow .15s,transform .15s;font-family:'Inter',sans-serif}
        .admin-btn-primary:hover{opacity:.92;transform:translateY(-1px);box-shadow:0 6px 16px rgba(99,102,241,.35);color:#fff}
        .admin-btn-secondary{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;background:#fff;color:#475569;font-size:13.5px;font-weight:600;border-radius:10px;border:1px solid #e2e8f0;cursor:pointer;text-decoration:none;transition:background .15s,border-color .15s;font-family:'Inter',sans-serif}
        .admin-btn-secondary:hover{background:#f8fafc;border-color:#cbd5e1;color:#1e293b}
        .admin-btn-danger{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;background:#fff1f2;color:#ef4444;font-size:13.5px;font-weight:700;border-radius:10px;border:1px solid #fecdd3;cursor:pointer;text-decoration:none;transition:background .15s;font-family:'Inter',sans-serif}
        .admin-btn-danger:hover{background:#fee2e2}
        .admin-btn-sm{padding:6px 14px!important;font-size:12px!important;border-radius:8px!important}
        .admin-btn-danger-sm{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:#fff1f2;color:#ef4444;font-size:12px;font-weight:600;border-radius:8px;border:1px solid #fecdd3;cursor:pointer;transition:background .15s;font-family:'Inter',sans-serif}
        .admin-btn-danger-sm:hover{background:#fee2e2}
        .admin-form-label{display:block;font-size:12.5px;font-weight:700;color:#374151;margin-bottom:6px}
        .admin-form-input,.admin-form-select{width:100%;padding:10px 14px;font-size:13.5px;color:#1e293b;background:#fff;border:1px solid #e2e8f0;border-radius:10px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:'Inter',sans-serif}
        .admin-form-input:focus,.admin-form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .admin-form-input::placeholder{color:#cbd5e1}
        .admin-form-select{appearance:none;cursor:pointer}
        .admin-form-hint{font-size:11.5px;color:#94a3b8;margin-top:5px;margin-bottom:0}
        .admin-form-error{font-size:11.5px;color:#ef4444;margin-top:4px;margin-bottom:0}
        .admin-form-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding-top:20px;margin-top:20px;border-top:1px solid #f1f5f9}
        /* Alerts */
        .admin-alert{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;font-size:13.5px;font-weight:600;margin-bottom:16px}
        .admin-alert-success{background:#ecfdf5;border-left:4px solid #10b981;color:#065f46}
        .admin-alert-error{background:#fff1f2;border-left:4px solid #ef4444;color:#7f1d1d}
        .admin-alert-warning{background:#fefce8;border-left:4px solid #f59e0b;color:#713f12}
        .admin-alert-info{background:#eff6ff;border-left:4px solid #3b82f6;color:#1e3a5f}
        /* Badges */
        .admin-badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:100px}
        .admin-badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
        .admin-badge-green{color:#065f46;background:#d1fae5}.admin-badge-green::before{background:#10b981}
        .admin-badge-red{color:#7f1d1d;background:#fee2e2}.admin-badge-red::before{background:#ef4444}
        .admin-badge-amber{color:#78350f;background:#fef3c7}.admin-badge-amber::before{background:#f59e0b}
        .admin-badge-blue{color:#1e3a5f;background:#dbeafe}.admin-badge-blue::before{background:#3b82f6}
        .admin-badge-indigo{color:#3730a3;background:#e0e7ff}.admin-badge-indigo::before{background:#6366f1}
        .admin-badge-gray{color:#374151;background:#f3f4f6}.admin-badge-gray::before{background:#9ca3af}
        .admin-badge-orange{color:#7c2d12;background:#ffedd5}.admin-badge-orange::before{background:#f97316}
        /* Search */
        .admin-search-wrap{position:relative;flex:1}
        .admin-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none}
        .admin-search-input{width:100%;padding:9px 14px 9px 38px;font-size:13.5px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;color:#1e293b;outline:none;transition:border-color .15s,box-shadow .15s}
        .admin-search-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .admin-search-input::placeholder{color:#cbd5e1}
        /* Empty state */
        .admin-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 20px;color:#94a3b8;text-align:center}
        .admin-empty-icon{width:56px;height:56px;background:#f1f5f9;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
        .admin-empty-title{font-size:15px;font-weight:700;color:#64748b;margin-bottom:4px}
        .admin-empty-sub{font-size:13px;color:#94a3b8}
        /* Upload zone */
        .admin-upload-zone{border:2px dashed #e2e8f0;border-radius:14px;padding:32px;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;background:#f8fafc}
        .admin-upload-zone:hover{border-color:#6366f1;background:#f5f3ff}
        /* Info grid */
        .admin-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
        .admin-info-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:4px}
        .admin-info-value{font-size:14px;font-weight:600;color:#1e293b}
        /* Utilities */
        .admin-panel-container{max-width:1400px}
        .glass{background:rgba(255,255,255,.8);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.5)}
        /* Pagination */
        nav[role=navigation]{margin-top:0}
        nav[role=navigation] a{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;font-size:13px;font-weight:600;border-radius:8px;border:1px solid #e2e8f0;color:#475569;text-decoration:none;background:#fff;transition:background .15s,border-color .15s}
        nav[role=navigation] a:hover{background:#f8fafc;border-color:#6366f1;color:#6366f1}
        nav[role=navigation] span[aria-current=page]>span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;font-size:13px;font-weight:700;border-radius:8px;background:#4f46e5;color:#fff;border:1px solid #4f46e5}
    </style>

    @stack('styles')
</head>

<body>
    <div style="display:flex; min-height:100vh;">

        <!-- ══ Sidebar ══ -->
        @php
            $__companyLogoModel = \App\Models\ThemeSetting::where('key', 'company_logo')->first();
            $__companyLogoUrl   = $__companyLogoModel ? $__companyLogoModel->company_logo : '';
            $__companyName      = \Smartisan\Settings\Facades\Settings::group('company')->get('company_name') ?: config('app.name');
            $__companyAddress   = \Smartisan\Settings\Facades\Settings::group('company')->get('company_address') ?: '';
            $__companyCity      = \Smartisan\Settings\Facades\Settings::group('company')->get('company_city') ?: '';
            $__companyPhone     = \Smartisan\Settings\Facades\Settings::group('company')->get('company_phone') ?: '';
            $__companyEmail     = \Smartisan\Settings\Facades\Settings::group('company')->get('company_email') ?: '';
        @endphp
        <aside class="admin-sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                @if($__companyLogoUrl)
                    <img src="{{ $__companyLogoUrl }}" alt="{{ $__companyName }}"
                         style="height:36px;width:auto;object-fit:contain;max-width:160px;flex-shrink:0;">
                @else
                    <div class="sidebar-logo-icon">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="sidebar-logo-text">{{ $__companyName }} <span>Admin</span></span>
                @endif
            </div>

            <!-- Nav -->
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>

                <div class="sidebar-section-label">Management</div>

                <a href="{{ route('admin.products.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    Products
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Categories
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.*') && !request()->routeIs('admin.orders.archived') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Orders
                    @php $unviewedOrders = \App\Models\Order::whereNull('admin_viewed_at')->count(); @endphp
                    <span id="sidebar-new-orders-badge" class="sidebar-badge" style="background:#ef4444;{{ $unviewedOrders > 0 ? '' : 'display:none;' }}">{{ $unviewedOrders ?: '' }}</span>
                </a>

                <a href="{{ route('admin.orders.archived') }}"
                   class="sidebar-link {{ request()->routeIs('admin.orders.archived') ? 'active' : '' }}"
                   style="padding-left: 2.5rem; font-size: 0.8rem; opacity: 0.85;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12"/>
                    </svg>
                    Archived Orders
                    @php $archivedCount = \App\Models\Order::onlyTrashed()->count(); @endphp
                    @if($archivedCount > 0)
                        <span class="sidebar-badge" style="background:#6366f1;">{{ $archivedCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.payments.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Payments
                </a>

                <a href="{{ route('admin.customers.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Customers
                </a>

                <a href="{{ route('admin.reviews.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Reviews
                </a>

                <a href="{{ route('admin.returns.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Returns &amp; Refunds
                    @php $unviewedReturns = \App\Models\ReturnAndRefund::whereNull('admin_viewed_at')->count(); @endphp
                    <span id="sidebar-returns-badge" class="sidebar-badge" style="{{ $unviewedReturns > 0 ? '' : 'display:none;' }}">{{ $unviewedReturns ?: '' }}</span>
                </a>

                <a href="{{ route('admin.messages.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Messages
                    @php $unread = \App\Models\ContactMessage::where('is_read', false)->count(); @endphp
                    @if($unread > 0)
                    <span class="sidebar-badge">{{ $unread }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.order-messages.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.order-messages.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Order Messages
                    @php $orderUnread = \App\Models\OrderMessage::where('sender_type', 'customer')->where('is_read', false)->count(); @endphp
                    <span id="sidebar-order-msg-badge" class="sidebar-badge" style="background:#ef4444;{{ $orderUnread > 0 ? '' : 'display:none;' }}">{{ $orderUnread ?: '' }}</span>
                </a>

                <a href="{{ route('admin.user-notifications.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.user-notifications*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Send Notifications
                    @php $recentNotifs = \App\Models\UserNotification::where('created_at', '>=', now()->subDay())->count(); @endphp
                    @if($recentNotifs > 0)
                        <span class="sidebar-badge" style="background:#6366f1;">{{ $recentNotifs }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.coupons.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Coupons
                </a>

                <a href="{{ route('admin.suppliers.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Suppliers
                </a>

                <a href="{{ route('admin.purchases.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Purchases
                </a>

                <a href="{{ route('admin.stock.report') }}"
                   class="sidebar-link {{ request()->routeIs('admin.stock.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Stock Report
                    @php $outStock = \App\Models\Product::withSum(['productStocks as sq' => fn($q) => $q->where('status', \App\Enums\Status::ACTIVE)], 'quantity')->get()->filter(fn($p) => ($p->sq ?? 0) <= 0)->count(); @endphp
                    @if($outStock > 0)
                        <span class="sidebar-badge" style="background:#ef4444;">{{ $outStock }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.stock-notifications.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.stock-notifications.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Stock Alerts
                    @php $pendingAlerts = \App\Models\StockNotification::where('notified', false)->count(); @endphp
                    @if($pendingAlerts > 0)
                        <span class="sidebar-badge" style="background:#f59e0b;">{{ $pendingAlerts }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.damages.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.damages.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Damages
                </a>

                <a href="{{ route('admin.barcodes.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.barcodes.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h1v12H4zm2 0h1v12H6zm3 0h2v12H9zm3 0h1v12h-1zm2 0h1v12h-1zm2 0h2v12h-2z"/>
                    </svg>
                    Barcodes
                </a>

                <a href="{{ route('admin.sliders.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Hero Sliders
                </a>

                <a href="{{ route('admin.promotions.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Promotions
                </a>

                <a href="{{ route('admin.brands.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Brands
                </a>

                <div class="sidebar-section-label">Content</div>

                <a href="{{ route('admin.pages.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Static Pages
                </a>

                <a href="{{ route('admin.faq.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    FAQ
                    @php $faqCount = \App\Models\FaqItem::where('is_active', true)->count(); @endphp
                    @if($faqCount > 0)
                    <span class="sidebar-badge" style="background:#6366f1">{{ $faqCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.blog.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    Blog Posts
                </a>

                <div class="sidebar-section-label">Settings</div>

                <a href="{{ route('admin.settings.site') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.site') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    Site Settings
                </a>

                <a href="{{ route('admin.settings.company') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.company') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Company
                </a>

                <a href="{{ route('admin.settings.notification-alerts') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.notification-alerts') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notification Alerts
                </a>

                <a href="{{ route('admin.settings.theme') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.theme*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Logos & Theme
                </a>

                <a href="{{ route('admin.settings.shipping') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.shipping') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    Shipping
                </a>

                <a href="{{ route('admin.settings.seo') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.seo*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    SEO Settings
                </a>

                <a href="{{ route('admin.settings.smtp') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.smtp*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Mail / SMTP
                </a>

                <a href="{{ route('admin.settings.integrations') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.integrations*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                    Integrations
                </a>

                <a href="{{ route('admin.settings.otp') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.otp*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21h14.878a2 2 0 001.94-1.515L22 17"/>
                    </svg>
                    Verification & OTP
                </a>

                <a href="{{ route('admin.settings.sms-gateway') }}"
                   class="sidebar-link {{ request()->routeIs('admin.settings.sms-gateway*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    SMS Gateway
                </a>

                <a href="{{ route('admin.payment-gateways.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Payment Gateways
                </a>

                <div class="sidebar-section-label">Advanced</div>

                <a href="{{ route('admin.finance.currency') }}"
                   class="sidebar-link {{ request()->routeIs('admin.finance.currency') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Currency
                </a>

                <a href="{{ route('admin.shipping.order-areas') }}"
                   class="sidebar-link {{ request()->routeIs('admin.shipping.order-areas') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Delivery Areas
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Admin Users
                </a>

                <a href="{{ route('admin.roles.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Roles
                </a>
            </nav>

            <!-- Footer -->
            <div class="sidebar-footer">
                <div style="padding:12px 4px 4px;border-top:1px solid rgba(255,255,255,0.06);">
                    <p style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.8);margin:0 0 4px;">{{ $__companyName }}</p>
                    @if($__companyAddress)
                        <p style="font-size:11px;color:rgba(255,255,255,0.4);margin:0 0 2px;line-height:1.4;">
                            {{ $__companyAddress }}@if($__companyCity), {{ $__companyCity }}@endif
                        </p>
                    @endif
                    @if($__companyPhone)
                        <p style="font-size:11px;color:rgba(255,255,255,0.35);margin:0 0 2px;">
                            <span style="opacity:.6;">&#9990;</span> {{ $__companyPhone }}
                        </p>
                    @endif
                    @if($__companyEmail)
                        <p style="font-size:11px;color:rgba(255,255,255,0.35);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <span style="opacity:.6;">&#9993;</span> {{ $__companyEmail }}
                        </p>
                    @endif
                    @if(!$__companyAddress && !$__companyPhone && !$__companyEmail)
                        <a href="{{ route('admin.settings.company') }}"
                           style="font-size:11px;color:#818cf8;text-decoration:none;">
                            &#9998; Add company details
                        </a>
                    @endif
                </div>
            </div>
        </aside>

        <!-- ══ Main ══ -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" style="display:none;" class="md-menu-btn">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <span style="font-size:13px;color:#94a3b8;font-weight:500;">
                        @yield('breadcrumb', '')
                    </span>
                </div>

                <div style="display:flex;align-items:center;gap:16px;">
                    <!-- ── Notification Bell ── -->
                    <div style="position:relative;" id="notif-wrapper">
                        <button id="notif-bell-btn"
                                style="background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;color:#64748b;line-height:0;position:relative;"
                                title="Notifications">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="notif-badge" style="display:none;position:absolute;top:2px;right:2px;min-width:16px;height:16px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;border-radius:8px;line-height:16px;text-align:center;padding:0 3px;"></span>
                        </button>

                        <!-- Dropdown Panel -->
                        <div id="notif-dropdown" style="display:none;position:absolute;top:calc(100% + 10px);right:-10px;width:360px;background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.15);border:1px solid #e2e8f0;z-index:1000;overflow:hidden;">
                            <!-- Header -->
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #f1f5f9;">
                                <span style="font-size:14px;font-weight:700;color:#0f172a;">Notifications</span>
                                <div style="display:flex;gap:8px;">
                                    <button id="notif-mark-read" style="font-size:11px;font-weight:600;color:#6366f1;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;" onmouseover="this.style.background='#eef2ff'" onmouseout="this.style.background='none'">Mark all read</button>
                                    <button id="notif-clear" style="font-size:11px;font-weight:600;color:#94a3b8;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">Clear all</button>
                                </div>
                            </div>

                            <!-- List -->
                            <div id="notif-list" style="max-height:400px;overflow-y:auto;">
                                <div id="notif-empty" style="padding:32px;text-align:center;color:#94a3b8;font-size:13px;">
                                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;opacity:.4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    No notifications yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div style="width:1px;height:28px;background:#e2e8f0;"></div>

                    <!-- User info -->
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img class="topbar-avatar"
                             src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=4F46E5&color=fff&bold=true"
                             alt="{{ auth()->user()->name ?? 'Admin' }}">
                        <div style="display:flex;flex-direction:column;line-height:1.3;">
                            <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <span style="font-size:11px;color:#94a3b8;">Super Administrator</span>
                        </div>
                    </div>

                    <!-- Sign out -->
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" style="font-size:12px;font-weight:600;color:#64748b;background:#f1f5f9;border:none;border-radius:8px;padding:6px 14px;cursor:pointer;transition:background 0.15s;"
                                onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                            Sign Out
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main class="admin-content">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer style="background:#fff;border-top:1px solid #e2e8f0;padding:16px 28px;text-align:center;font-size:12.5px;color:#94a3b8;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </footer>
        </div>
    </div>

    <!-- ══ Professional Confirm Modal ══ -->
    <div id="confirmModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <!-- Backdrop -->
        <div id="confirmBackdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(4px);" onclick="closeConfirmModal()"></div>
        <!-- Dialog -->
        <div id="confirmDialog" style="position:relative;width:100%;max-width:420px;background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,0.18);padding:32px;transform:scale(0.95);opacity:0;transition:transform 0.2s ease,opacity 0.2s ease;">
            <!-- Icon -->
            <div id="confirmIcon" style="width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;background:#fef2f2;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <!-- Text -->
            <h3 id="confirmTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0 0 8px;"></h3>
            <p id="confirmMessage" style="font-size:14px;color:#64748b;margin:0 0 28px;line-height:1.6;"></p>
            <!-- Buttons -->
            <div style="display:flex;gap:10px;">
                <button onclick="closeConfirmModal()" style="flex:1;padding:11px 20px;border-radius:10px;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:13.5px;font-weight:600;cursor:pointer;transition:background 0.15s;font-family:'Inter',sans-serif;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    Cancel
                </button>
                <button id="confirmBtn" style="flex:1;padding:11px 20px;border-radius:10px;border:none;font-size:13.5px;font-weight:700;cursor:pointer;transition:opacity 0.15s;font-family:'Inter',sans-serif;color:#fff;background:#ef4444;" onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
    // ── Confirm Modal ──────────────────────────────────────────
    let _confirmCallback = null;

    function showConfirm({ title, message, confirmText = 'Confirm', type = 'danger', hideCancel = false, onConfirm }) {
        _confirmCallback = onConfirm;

        document.getElementById('confirmTitle').textContent   = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmBtn').textContent     = confirmText;

        const iconEl = document.getElementById('confirmIcon');
        const btnEl  = document.getElementById('confirmBtn');
        const cancelBtn = document.querySelector('#confirmDialog button[onclick="closeConfirmModal()"]');
        if (cancelBtn) cancelBtn.style.display = hideCancel ? 'none' : 'block';

        const themes = {
            danger:  { bg: '#fef2f2', stroke: '#ef4444', btnBg: '#ef4444' },
            warning: { bg: '#fffbeb', stroke: '#f59e0b', btnBg: '#f59e0b' },
            success: { bg: '#ecfdf5', stroke: '#10b981', btnBg: '#10b981' },
            info:    { bg: '#eff6ff', stroke: '#3b82f6', btnBg: '#4f46e5' },
        };
        const t = themes[type] || themes.danger;

        iconEl.style.background = t.bg;
        iconEl.querySelector('svg').setAttribute('stroke', t.stroke);
        btnEl.style.background  = t.btnBg;

        const modal  = document.getElementById('confirmModal');
        const dialog = document.getElementById('confirmDialog');
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            dialog.style.transform = 'scale(1)';
            dialog.style.opacity   = '1';
        });
    }

    function closeConfirmModal() {
        const modal  = document.getElementById('confirmModal');
        const dialog = document.getElementById('confirmDialog');
        dialog.style.transform = 'scale(0.95)';
        dialog.style.opacity   = '0';
        setTimeout(() => { modal.style.display = 'none'; }, 180);
        _confirmCallback = null;
    }

    document.getElementById('confirmBtn').addEventListener('click', function () {
        if (_confirmCallback) _confirmCallback();
        closeConfirmModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeConfirmModal();
    });

    // Helper: confirm then submit a form by id
    function confirmSubmit(formId, options) {
        showConfirm({ ...options, onConfirm: () => document.getElementById(formId).submit() });
    }

    // Helper: show a simple alert modal
    function showAlert(options) {
        showConfirm({ ...options, hideCancel: true, confirmText: options.btnText || 'Got it' });
    }
    </script>

    <script src="{{ mix('js/app.js') }}"></script>

    {{-- Global client-side search for admin tables --}}
    <script>
    function adminSearch(inputId, targetId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const target = document.getElementById(targetId);
            if (!target) return;

            // Table rows
            const rows = target.querySelectorAll('tbody tr');
            if (rows.length) {
                let visible = 0;
                rows.forEach(function (row) {
                    const text = row.innerText.toLowerCase();
                    const show = !q || text.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                // show/hide empty state
                let empty = target.querySelector('.admin-search-empty');
                if (!empty) {
                    empty = document.createElement('tr');
                    empty.className = 'admin-search-empty';
                    empty.innerHTML = '<td colspan="20" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;">No results found.</td>';
                    target.querySelector('tbody').appendChild(empty);
                }
                empty.style.display = (visible === 0 && q) ? '' : 'none';
                return;
            }

            // Card grid (brands, pages, payment gateways, users)
            const cards = target.querySelectorAll('[data-search-item]');
            if (cards.length) {
                let visible = 0;
                cards.forEach(function (card) {
                    const text = card.innerText.toLowerCase();
                    const show = !q || text.includes(q);
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                let empty = target.querySelector('.admin-search-empty');
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'admin-search-empty col-span-full';
                    empty.style.cssText = 'padding:40px;text-align:center;color:#94a3b8;font-size:13px;';
                    empty.textContent = 'No results found.';
                    target.appendChild(empty);
                }
                empty.style.display = (visible === 0 && q) ? '' : 'none';
            }
        });
    }
    </script>

    @stack('scripts')

    {{-- ══ Admin Notification Polling + Bell Dropdown ══ --}}
    <script>
    (function () {
        var POLL_URL       = '{{ route('admin.notifications.poll') }}';
        var MARK_READ_URL  = '{{ route('admin.notifications.mark-read') }}';
        var CLEAR_URL      = '{{ route('admin.notifications.clear') }}';
        var ORDERS_URL     = '{{ route('admin.orders.index') }}';
        var MSGS_URL       = '{{ route('admin.order-messages.index') }}';
        var CSRF           = '{{ csrf_token() }}';
        var INTERVAL       = 30000; // 30 s
        var lastMs = Date.now();

        // ── Bell UI elements ──
        var bellBtn     = document.getElementById('notif-bell-btn');
        var bellBadge   = document.getElementById('notif-badge');
        var dropdown    = document.getElementById('notif-dropdown');
        var notifList   = document.getElementById('notif-list');
        var emptyState  = document.getElementById('notif-empty');
        var markReadBtn = document.getElementById('notif-mark-read');
        var clearBtn    = document.getElementById('notif-clear');

        // Toggle dropdown
        bellBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = dropdown.style.display !== 'none';
            dropdown.style.display = open ? 'none' : 'block';
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!document.getElementById('notif-wrapper').contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Mark all read
        markReadBtn.addEventListener('click', function () {
            fetch(MARK_READ_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function () {
                setBellBadge(0);
                notifList.querySelectorAll('.notif-item').forEach(function (el) {
                    el.style.background = '#fff';
                });
            });
        });

        // Clear all
        clearBtn.addEventListener('click', function () {
            fetch(CLEAR_URL, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function () {
                notifList.innerHTML = '';
                notifList.appendChild(emptyState);
                emptyState.style.display = '';
                setBellBadge(0);
            });
        });

        function setBellBadge(count) {
            if (!bellBadge) return;
            if (count > 0) {
                bellBadge.textContent = count > 99 ? '99+' : count;
                bellBadge.style.display = '';
            } else {
                bellBadge.style.display = 'none';
            }
        }

        var iconMap = {
            cart:    '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
            message: '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
            warning: '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            return:  '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>',
            payment: '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            bell:    '<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
        };

        var colorMap = {
            order: '#6366f1', message: '#f59e0b', cancellation: '#ef4444',
            return: '#3b82f6', payment: '#10b981', stock: '#f97316', bell: '#94a3b8',
        };

        var renderedIds = new Set();

        function renderNotifications(notifications) {
            if (!notifications || notifications.length === 0) {
                emptyState.style.display = '';
                return;
            }
            emptyState.style.display = 'none';

            notifications.forEach(function (n) {
                if (renderedIds.has(n.id)) {
                    // Update read state if changed
                    var existing = document.getElementById('notif-item-' + n.id);
                    if (existing && n.is_read) existing.style.background = '#fff';
                    return;
                }
                renderedIds.add(n.id);

                var color = colorMap[n.type] || '#94a3b8';
                var icon  = iconMap[n.icon] || iconMap['bell'];
                var time  = timeAgo(n.created_at);

                var el = document.createElement('div');
                el.id = 'notif-item-' + n.id;
                el.className = 'notif-item';
                el.style.cssText = 'display:flex;gap:10px;padding:12px 16px;border-bottom:1px solid #f8fafc;cursor:pointer;transition:background .1s;background:' + (n.is_read ? '#fff' : '#f8faff') + ';';
                el.onmouseover = function() { this.style.background = '#f1f5f9'; };
                el.onmouseout  = function() { this.style.background = n.is_read ? '#fff' : '#f8faff'; };
                if (n.link) el.onclick = function() { window.location.href = n.link; };

                el.innerHTML =
                    '<div style="width:30px;height:30px;border-radius:8px;background:' + color + '20;color:' + color + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + icon + '</div>' +
                    '<div style="flex:1;min-width:0;">' +
                        '<p style="font-size:12.5px;font-weight:' + (n.is_read ? '500' : '700') + ';color:#0f172a;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(n.title) + '</p>' +
                        (n.body ? '<p style="font-size:11.5px;color:#64748b;margin:0 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escHtml(n.body) + '</p>' : '') +
                        '<p style="font-size:10px;color:#94a3b8;margin:0;">' + time + '</p>' +
                    '</div>' +
                    (!n.is_read ? '<div style="width:7px;height:7px;border-radius:50%;background:#6366f1;flex-shrink:0;margin-top:6px;"></div>' : '');

                // Prepend so newest is on top
                if (notifList.firstChild && notifList.firstChild !== emptyState) {
                    notifList.insertBefore(el, notifList.firstChild);
                } else {
                    notifList.appendChild(el);
                }
            });
        }

        function timeAgo(dateStr) {
            var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
            if (diff < 60)   return 'Just now';
            if (diff < 3600) return Math.floor(diff/60) + 'm ago';
            if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
            return Math.floor(diff/86400) + 'd ago';
        }

        function escHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        // ── Sidebar badges ──
        var orderBadge   = document.getElementById('sidebar-new-orders-badge');
        var msgBadge     = document.getElementById('sidebar-order-msg-badge');
        var returnsBadge = document.getElementById('sidebar-returns-badge');

        // ── Toast container ──
        var toastBox = document.createElement('div');
        toastBox.id = 'admin-toast-box';
        Object.assign(toastBox.style, {
            position:'fixed', bottom:'24px', right:'24px', zIndex:'99999',
            display:'flex', flexDirection:'column', gap:'10px', alignItems:'flex-end',
        });
        document.body.appendChild(toastBox);

        function toast(msg, type, url) {
            var colors = { order:'#10b981', msg:'#f59e0b', cancel:'#ef4444' };
            var bg = colors[type] || '#4f46e5';
            var t = document.createElement('div');
            t.innerHTML = msg;
            Object.assign(t.style, {
                background: bg, color:'#fff', padding:'12px 18px',
                borderRadius:'12px', fontSize:'13px', fontWeight:'600',
                boxShadow:'0 8px 24px rgba(0,0,0,0.18)', cursor: url ? 'pointer' : 'default',
                maxWidth:'320px', lineHeight:'1.4', opacity:'0',
                transform:'translateY(8px)', transition:'opacity 0.25s, transform 0.25s',
            });
            if (url) t.onclick = function() { window.location.href = url; };
            toastBox.appendChild(t);
            requestAnimationFrame(function() {
                t.style.opacity = '1'; t.style.transform = 'translateY(0)';
            });
            setTimeout(function() {
                t.style.opacity = '0'; t.style.transform = 'translateY(8px)';
                setTimeout(function() { t.remove(); }, 300);
            }, 6000);
        }

        function browserNotify(title, body, url) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;
            var n = new Notification(title, { body: body, icon: '/favicon.ico' });
            if (url) n.onclick = function() { window.focus(); window.location.href = url; };
        }

        function setBadge(el, count) {
            if (!el) return;
            if (count > 0) { el.textContent = count > 99 ? '99+' : count; el.style.display = ''; }
            else { el.style.display = 'none'; }
        }

        function poll() {
            fetch(POLL_URL + '?since=' + lastMs, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(function(r) { return r.ok ? r.json() : null; })
            .catch(function() { return null; })
            .then(function(data) {
                if (!data) return;
                lastMs = data.server_time || Date.now();

                // ── Sidebar orders badge ──
                setBadge(orderBadge, data.unviewed_total);

                // ── New orders ──
                if (data.new_orders > 0) {
                    data.orders.forEach(function(o) {
                        var msg = '🛒 New Order <strong>#' + o.order_serial_no + '</strong><br>'
                            + '{{ $currencySymbol ?? '₹' }}' + parseFloat(o.total).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                        toast(msg, 'order', ORDERS_URL);
                        browserNotify('🛒 New Order #' + o.order_serial_no, 'Total: {{ $currencySymbol ?? '₹' }}' + parseFloat(o.total).toFixed(2), ORDERS_URL);
                    });
                    try {
                        var ctx = new (window.AudioContext || window.webkitAudioContext)();
                        var osc = ctx.createOscillator(); var gain = ctx.createGain();
                        osc.connect(gain); gain.connect(ctx.destination);
                        osc.frequency.value = 880;
                        gain.gain.setValueAtTime(0.15, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                        osc.start(); osc.stop(ctx.currentTime + 0.4);
                    } catch(e) {}
                }

                // ── Unread messages badge ──
                setBadge(msgBadge, data.unread_msgs);

                // ── Cancellations ──
                if (data.cancellations > 0) {
                    toast('⚠️ <strong>' + data.cancellations + ' cancellation request(s)</strong> need your attention', 'cancel', MSGS_URL + '?filter=cancellation');
                    browserNotify('⚠ Cancellation Request', data.cancellations + ' order(s) need your review', MSGS_URL + '?filter=cancellation');
                }

                // ── Returns badge ──
                setBadge(returnsBadge, data.unviewed_returns);
                if (data.new_returns > 0) {
                    var returnsUrl = '{{ route('admin.returns.index') }}';
                    toast('↩ <strong>' + data.new_returns + ' return request(s)</strong> submitted', 'msg', returnsUrl);
                    browserNotify('↩ New Return Request', data.new_returns + ' customer(s) submitted a return request', returnsUrl);
                }

                // ── Bell dropdown ──
                if (data.notifications) {
                    renderNotifications(data.notifications);
                }
                setBellBadge(data.unread_notif_count || 0);
            });
        }

        // Initial load of notifications on page load
        fetch(POLL_URL + '?since=0', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.ok ? r.json() : null; })
        .then(function(data) {
            if (!data) return;
            if (data.notifications) renderNotifications(data.notifications);
            setBellBadge(data.unread_notif_count || 0);
            setBadge(orderBadge, data.unviewed_total);
            setBadge(msgBadge, data.unread_msgs);
            setBadge(returnsBadge, data.unviewed_returns);
            lastMs = data.server_time || Date.now();
        });

        // Request browser notification permission on first interaction
        if ('Notification' in window && Notification.permission === 'default') {
            document.addEventListener('click', function req() {
                Notification.requestPermission();
                document.removeEventListener('click', req);
            }, { once: true });
        }

        // Start polling every 30s
        setTimeout(function loop() { poll(); setTimeout(loop, INTERVAL); }, INTERVAL);
    })();
    </script>
    @stack('modals')
</body>
</html>