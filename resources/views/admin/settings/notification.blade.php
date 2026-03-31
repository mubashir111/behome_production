@extends('layouts.admin')

@section('title', 'Notification Settings')

@section('content')
@php
    $fcm_secret = old('notification_fcm_secret_key', data_get($settings, 'notification_fcm_secret_key', ''));
    $fcm_vapid = old('notification_fcm_public_vapid_key', data_get($settings, 'notification_fcm_public_vapid_key', ''));
    $fcm_api = old('notification_fcm_api_key', data_get($settings, 'notification_fcm_api_key', ''));
    $fcm_auth = old('notification_fcm_auth_domain', data_get($settings, 'notification_fcm_auth_domain', ''));
    $fcm_project = old('notification_fcm_project_id', data_get($settings, 'notification_fcm_project_id', ''));
    $fcm_bucket = old('notification_fcm_storage_bucket', data_get($settings, 'notification_fcm_storage_bucket', ''));
    $fcm_sender = old('notification_fcm_messaging_sender_id', data_get($settings, 'notification_fcm_messaging_sender_id', ''));
    $fcm_app = old('notification_fcm_app_id', data_get($settings, 'notification_fcm_app_id', ''));
    $fcm_measure = old('notification_fcm_measurement_id', data_get($settings, 'notification_fcm_measurement_id', ''));
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Notification Settings</h2>
            <p class="admin-page-subtitle">Configure Firebase Cloud Messaging (FCM) for push notifications.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.notification.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Firebase Configuration -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-sm font-bold">01</span>
                FCM Credentials
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">FCM Secret Key</label>
                    <input type="text" name="notification_fcm_secret_key" value="{{ $fcm_secret }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Public VAPID Key</label>
                    <input type="text" name="notification_fcm_public_vapid_key" value="{{ $fcm_vapid }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">API Key</label>
                    <input type="text" name="notification_fcm_api_key" value="{{ $fcm_api }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Auth Domain</label>
                    <input type="text" name="notification_fcm_auth_domain" value="{{ $fcm_auth }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Project ID</label>
                    <input type="text" name="notification_fcm_project_id" value="{{ $fcm_project }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Storage Bucket</label>
                    <input type="text" name="notification_fcm_storage_bucket" value="{{ $fcm_bucket }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Messaging Sender ID</label>
                    <input type="text" name="notification_fcm_messaging_sender_id" value="{{ $fcm_sender }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">App ID</label>
                    <input type="text" name="notification_fcm_app_id" value="{{ $fcm_app }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Measurement ID</label>
                    <input type="text" name="notification_fcm_measurement_id" value="{{ $fcm_measure }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-orange-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn-primary">Save Notification Settings</button>
        </div>
    </form>
</div>
@endsection
