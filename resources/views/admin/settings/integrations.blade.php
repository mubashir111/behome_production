@extends('layouts.admin')

@section('title', 'Integrations')

@section('content')
@php
    $stripe_refund_enabled    = old('stripe_refund_enabled', data_get($settings, 'stripe_refund_enabled', 10));
    $google_client_id         = old('google_client_id', data_get($settings, 'google_client_id', ''));
    $facebook_app_id          = old('facebook_app_id', data_get($settings, 'facebook_app_id', ''));
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Integrations</h2>
            <p class="admin-page-subtitle">Manage third-party API keys and service connections.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.integrations.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Stripe Refund -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Stripe Automatic Refunds
            </h3>

            <!-- Info Box -->
            <div class="mb-6 rounded-2xl border-2 border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 shrink-0 text-slate-500" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-slate-600">This key is used to automatically issue refunds via the Stripe API when a return is approved. Use your <strong>Stripe Secret Key</strong> from the Stripe Dashboard → Developers → API Keys. For live refunds use a <code>sk_live_…</code> key; for testing use <code>sk_test_…</code>.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Stripe Refund</label>
                    <select name="stripe_refund_enabled"
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                        <option value="5"  {{ (int)$stripe_refund_enabled === 5  ? 'selected' : '' }}>Enabled</option>
                        <option value="10" {{ (int)$stripe_refund_enabled === 10 ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">When enabled, refunds are issued automatically via Stripe on approval.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Stripe Secret Key</label>
                    <div class="relative">
                        <input type="password" name="stripe_refund_secret_key" id="stripe_secret_field"
                               value="{{ old('stripe_refund_secret_key', data_get($settings, 'stripe_refund_secret_key', '')) }}"
                               placeholder="sk_live_… or sk_test_…"
                               class="w-full px-5 py-3 pr-14 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                        <button type="button" onclick="toggleVisibility('stripe_secret_field')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 02: Google -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-sm font-bold">02</span>
                Google OAuth
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Google Client ID</label>
                    <input type="text" name="google_client_id" value="{{ $google_client_id }}"
                           placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-red-500 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Google Client Secret</label>
                    <div class="relative">
                        <input type="password" name="google_client_secret" id="google_secret_field"
                               value="{{ old('google_client_secret', data_get($settings, 'google_client_secret', '')) }}"
                               placeholder="GOCSPX-…"
                               class="w-full px-5 py-3 pr-14 rounded-2xl border-2 border-slate-300 focus:border-red-500 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                        <button type="button" onclick="toggleVisibility('google_secret_field')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 03: Facebook -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">03</span>
                Facebook / Meta Login
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Facebook App ID</label>
                    <input type="text" name="facebook_app_id" value="{{ $facebook_app_id }}"
                           placeholder="1234567890123456"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Facebook App Secret</label>
                    <div class="relative">
                        <input type="password" name="facebook_app_secret" id="facebook_secret_field"
                               value="{{ old('facebook_app_secret', data_get($settings, 'facebook_app_secret', '')) }}"
                               placeholder="abcdef1234567890abcdef1234567890"
                               class="w-full px-5 py-3 pr-14 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                        <button type="button" onclick="toggleVisibility('facebook_secret_field')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6">
            <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                Save Integration Settings
            </button>
        </div>
    </form>
</div>

<script>
function toggleVisibility(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection
