@extends('layouts.admin')

@section('title', 'Mail / SMTP Settings')

@section('content')
@php
    $mail_host        = old('mail_host', data_get($settings, 'mail_host', ''));
    $mail_port        = old('mail_port', data_get($settings, 'mail_port', ''));
    $mail_username    = old('mail_username', data_get($settings, 'mail_username', ''));
    $mail_encryption  = old('mail_encryption', data_get($settings, 'mail_encryption', 'ssl'));
    $mail_from_name   = old('mail_from_name', data_get($settings, 'mail_from_name', ''));
    $mail_from_email  = old('mail_from_email', data_get($settings, 'mail_from_email', ''));
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Mail / SMTP Settings</h2>
            <p class="admin-page-subtitle">Configure outgoing email for order confirmations, notifications, and system alerts.</p>
        </div>
    </div>

    @include('admin._alerts')

    <!-- Gmail Setup Info Box -->
    <div class="mb-6 rounded-2xl border-2 border-amber-300 bg-amber-50 p-5">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 shrink-0 text-amber-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-amber-800 mb-1">Gmail App Password Setup</p>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                    <li>Do <strong>not</strong> use your regular Gmail account password here.</li>
                    <li>First enable <strong>2-Step Verification</strong> on your Google account.</li>
                    <li>Then go to <strong>Google Account → Security → App Passwords</strong> and generate an App Password.</li>
                    <li>Use the 16-character App Password in the password field below.</li>
                    <li>Recommended settings: <code>Host: smtp.gmail.com</code>, <code>Port: 465</code>, <code>Encryption: ssl</code>.</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.settings.smtp.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Server -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                SMTP Server
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mail Host</label>
                    <input type="text" name="mail_host" value="{{ $mail_host }}"
                           placeholder="smtp.gmail.com"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mail Port</label>
                    <input type="text" name="mail_port" value="{{ $mail_port }}"
                           placeholder="465"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Common: 465 (SSL) or 587 (TLS/STARTTLS).</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Username</label>
                    <input type="text" name="mail_username" value="{{ $mail_username }}"
                           placeholder="youraddress@gmail.com"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="mail_password" id="mail_password_field"
                               placeholder="App Password (16 chars)"
                               class="w-full px-5 py-3 pr-14 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                        <button type="button" onclick="toggleVisibility('mail_password_field', 'toggle_mail_pw')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg id="toggle_mail_pw" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Leave blank to keep the current password.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Encryption</label>
                    <select name="mail_encryption"
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                        <option value="ssl"  {{ $mail_encryption === 'ssl'  ? 'selected' : '' }}>SSL</option>
                        <option value="tls"  {{ $mail_encryption === 'tls'  ? 'selected' : '' }}>TLS / STARTTLS</option>
                        <option value="none" {{ $mail_encryption === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 02: Sender Identity -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">02</span>
                Sender Identity
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $mail_from_name }}"
                           placeholder="Behom"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Display name shown to email recipients.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">From Email Address</label>
                    <input type="email" name="mail_from_email" value="{{ $mail_from_email }}"
                           placeholder="noreply@yourdomain.com"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                Save Mail Settings
            </button>
        </div>
    </form>

    <!-- Test Email Section -->
    <div class="admin-card mt-8">
        <h3 class="admin-section-title">
            <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-bold">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </span>
            Send Test Email
        </h3>

        <p class="text-sm text-slate-500 mb-4">After saving your settings, send a test email to verify your SMTP configuration is working correctly.</p>

        <form action="{{ route('admin.settings.smtp.test') }}" method="POST" class="flex items-end gap-4 flex-wrap">
            @csrf
            <div class="flex-1 min-w-[240px]">
                <label class="block text-sm font-bold text-slate-700 mb-2">Send Test Email To</label>
                <input type="email" name="test_email" value="{{ old('test_email') }}"
                       placeholder="you@example.com" required
                       class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-sky-600 outline-none transition-all bg-white text-slate-900">
            </div>
            <button type="submit"
                    class="px-8 py-3 rounded-2xl bg-sky-600 text-white font-bold hover:bg-sky-700 transition-all shadow-lg shadow-sky-200 whitespace-nowrap">
                Send Test Email
            </button>
        </form>
    </div>
</div>

<script>
function toggleVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
@endsection
