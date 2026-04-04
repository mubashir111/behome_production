@extends('layouts.admin')

@section('title', 'Verification & OTP')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Verification & OTP Settings</h2>
            <p class="admin-page-subtitle">Manage user registration requirements and OTP security parameters.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.otp.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-8">
            
            <!-- Section 01: User Registration Rules -->
            <div class="admin-card overflow-hidden border-2 border-emerald-100 shadow-xl shadow-emerald-50/50">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-outfit text-lg font-bold text-slate-800">New User Registration Controls</h3>
                        <p class="text-sm text-slate-400">Decide if new users must verify their identity before ordering.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @php 
                        $phone_verify = \Smartisan\Settings\Facades\Settings::group('site')->get('site_phone_verification');
                        $email_verify = \Smartisan\Settings\Facades\Settings::group('site')->get('site_email_verification');
                    @endphp
                    
                    <div class="admin-toggle-row p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="admin-toggle-label font-bold text-slate-700">Phone Verification Required?</span>
                            <p class="text-xs text-slate-400 mt-1">Users must confirm their mobile number via OTP.</p>
                        </div>
                        <select name="site_phone_verification" class="admin-select !w-32 border-2 {{ $phone_verify == 5 ? 'border-emerald-200 text-emerald-600' : 'border-slate-200 text-slate-400' }}">
                            <option value="5" {{ $phone_verify == 5 ? 'selected' : '' }}>YES</option>
                            <option value="10" {{ $phone_verify == 10 ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>

                    <div class="admin-toggle-row p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="admin-toggle-label font-bold text-slate-700">Email Verification Required?</span>
                            <p class="text-xs text-slate-400 mt-1">Users must confirm their email via unique token.</p>
                        </div>
                        <select name="site_email_verification" class="admin-select !w-32 border-2 {{ $email_verify == 5 ? 'border-indigo-200 text-indigo-600' : 'border-slate-200 text-slate-400' }}">
                            <option value="5" {{ $email_verify == 5 ? 'selected' : '' }}>YES</option>
                            <option value="10" {{ $email_verify == 10 ? 'selected' : '' }}>NO</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 02: OTP Technical Configuration -->
            <div class="admin-card overflow-hidden">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-outfit text-lg font-bold text-slate-800">OTP Technical Configuration</h3>
                        <p class="text-sm text-slate-400">Configure how OTP codes are generated and delivered.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="admin-form-group">
                        <label class="admin-label">Deliver Via</label>
                        <select name="otp_type" class="admin-select">
                            <option value="both" {{ data_get($settings, 'otp_type') == 'both' ? 'selected' : '' }}>Both (SMS & Email)</option>
                            <option value="sms" {{ data_get($settings, 'otp_type') == 'sms' ? 'selected' : '' }}>SMS Only</option>
                            <option value="email" {{ data_get($settings, 'otp_type') == 'email' ? 'selected' : '' }}>Email Only</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Code Length</label>
                        <select name="otp_digit_limit" class="admin-select">
                            <option value="4" {{ data_get($settings, 'otp_digit_limit') == 4 ? 'selected' : '' }}>4 Digits</option>
                            <option value="6" {{ data_get($settings, 'otp_digit_limit') == 6 ? 'selected' : '' }}>6 Digits</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Expiry (Minutes)</label>
                        <input type="number" name="otp_expire_time" value="{{ data_get($settings, 'otp_expire_time', 5) }}" class="admin-input" min="1" max="60">
                    </div>
                </div>
            </div>

            <!-- Global Action Bar -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="admin-btn-primary px-16 py-4 shadow-xl shadow-indigo-100 text-lg font-bold">
                    Save Verification & OTP Settings
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
