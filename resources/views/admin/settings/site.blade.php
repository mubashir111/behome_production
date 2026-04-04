@extends('layouts.admin')

@section('title', 'Global Site Settings')

@section('content')
@php
    $site_date_format = old('site_date_format', data_get($settings, 'site_date_format', 'Y-m-d'));
    $site_time_format = old('site_time_format', data_get($settings, 'site_time_format', 'H:i'));
    $site_timezone = old('site_default_timezone', data_get($settings, 'site_default_timezone', 'UTC'));
    $site_language = old('site_default_language', data_get($settings, 'site_default_language', 1));
    $site_currency = old('site_default_currency', data_get($settings, 'site_default_currency', ''));
    $site_currency_pos = old('site_currency_position', data_get($settings, 'site_currency_position', 5));
    $site_decimal = old('site_digit_after_decimal_point', data_get($settings, 'site_digit_after_decimal_point', 2));
    $site_copyright = old('site_copyright', data_get($settings, 'site_copyright', ''));
    $site_email_verify = old('site_email_verification', data_get($settings, 'site_email_verification', 10));
    $site_android = old('site_android_app_link', data_get($settings, 'site_android_app_link', ''));
    $site_ios = old('site_ios_app_link', data_get($settings, 'site_ios_app_link', ''));
    $site_max_qty = old('site_non_purchase_product_maximum_quantity', data_get($settings, 'site_non_purchase_product_maximum_quantity', 10));
    $site_debug = old('site_app_debug', data_get($settings, 'site_app_debug', 10));
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Global Site Settings</h2>
            <p class="admin-page-subtitle">Configure system-wide localization and financials.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.site.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Localization -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Localization & Display
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Date Format</label>
                    <input type="text" name="site_date_format" value="{{ $site_date_format }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">e.g. Y-m-d, d/m/Y</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Time Format</label>
                    <input type="text" name="site_time_format" value="{{ $site_time_format }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">e.g. H:i, h:i A</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Timezone</label>
                    <select name="site_default_timezone" required
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ $site_timezone == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Language ID</label>
                    <input type="number" name="site_default_language" value="{{ $site_language }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 02: Financials -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">02</span>
                Currency & Financials
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Currency</label>
                    <select name="site_default_currency" required
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                        @foreach(\App\Models\Currency::all() as $curr)
                            <option value="{{ $curr->id }}" {{ $site_currency == $curr->id ? 'selected' : '' }}>{{ $curr->name }} ({{ $curr->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Currency Position</label>
                    <select name="site_currency_position" required
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                        <option value="5" {{ $site_currency_pos == 5 ? 'selected' : '' }}>Left</option>
                        <option value="10" {{ $site_currency_pos == 10 ? 'selected' : '' }}>Right</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Decimals After Point</label>
                    <input type="number" name="site_digit_after_decimal_point" value="{{ $site_decimal }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Copyright Text</label>
                    <input type="text" name="site_copyright" value="{{ $site_copyright }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 03: Mobile App Store Links -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">03</span>
                Mobile App Store Links
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Android App Link</label>
                    <input type="text" name="site_android_app_link" value="{{ $site_android }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">iOS App Link</label>
                    <input type="text" name="site_ios_app_link" value="{{ $site_ios }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 04: System -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">04</span>
                System & Debug
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Max Quantity (Non-Purchase)</label>
                    <input type="number" name="site_non_purchase_product_maximum_quantity" value="{{ $site_max_qty }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-amber-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">App Debug Mode</label>
                    <select name="site_app_debug" required
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-amber-600 outline-none transition-all bg-white text-slate-900">
                        <option value="5" {{ $site_debug == 5 ? 'selected' : '' }}>ON (Development)</option>
                        <option value="10" {{ $site_debug == 10 ? 'selected' : '' }}>OFF (Production)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Hidden Requirements -->
        <input type="hidden" name="site_language_switch" value="{{ data_get($settings, 'site_language_switch', 10) }}">
        <input type="hidden" name="site_auto_update" value="{{ data_get($settings, 'site_auto_update', 10) }}">
        <input type="hidden" name="site_online_payment_gateway" value="{{ data_get($settings, 'site_online_payment_gateway', 10) }}">
        <input type="hidden" name="site_cash_on_delivery" value="{{ data_get($settings, 'site_cash_on_delivery', 5) }}">
        <input type="hidden" name="site_is_return_product_price_add_to_credit" value="{{ data_get($settings, 'site_is_return_product_price_add_to_credit', 10) }}">

        <div class="flex items-center justify-end gap-3 pt-6">
            <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                Save Global Settings
            </button>
        </div>
    </form>
</div>
@endsection
