@extends('layouts.admin')

@section('title', 'Company Settings')

@section('content')
@php
    $company_name = old('company_name', data_get($settings, 'company_name', ''));
    $company_email = old('company_email', data_get($settings, 'company_email', ''));
    $company_calling_code = old('company_calling_code', data_get($settings, 'company_calling_code', ''));
    $company_phone = old('company_phone', data_get($settings, 'company_phone', ''));
    $company_website = old('company_website', data_get($settings, 'company_website', ''));
    $company_city = old('company_city', data_get($settings, 'company_city', ''));
    $company_state = old('company_state', data_get($settings, 'company_state', ''));
    $company_country_code = old('company_country_code', data_get($settings, 'company_country_code', ''));
    $company_zip_code = old('company_zip_code', data_get($settings, 'company_zip_code', ''));
    $company_latitude = old('company_latitude', data_get($settings, 'company_latitude', ''));
    $company_longitude = old('company_longitude', data_get($settings, 'company_longitude', ''));
    $company_address = old('company_address', data_get($settings, 'company_address', ''));
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Company Settings</h2>
            <p class="admin-page-subtitle">Manage your business information and contact details.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.company.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Identification -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Company Identity
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Company Name</label>
                    <input type="text" name="company_name" value="{{ $company_name }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Company Email</label>
                    <input type="email" name="company_email" value="{{ $company_email }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Website (Optional)</label>
                    <input type="url" name="company_website" value="{{ $company_website }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 02: Contact -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">02</span>
                Contact Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Calling Code</label>
                    <input type="text" name="company_calling_code" value="{{ $company_calling_code }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900"
                           placeholder="e.g. +1">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
                    <input type="text" name="company_phone" value="{{ $company_phone }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 03: Location -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">03</span>
                Business Location
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">City</label>
                    <input type="text" name="company_city" value="{{ $company_city }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">State / Province</label>
                    <input type="text" name="company_state" value="{{ $company_state }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Country Code</label>
                    <input type="text" name="company_country_code" value="{{ $company_country_code }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900"
                           placeholder="e.g. US">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Zip Code</label>
                    <input type="text" name="company_zip_code" value="{{ $company_zip_code }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Address</label>
                    <textarea name="company_address" rows="3" required
                              class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">{{ $company_address }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Latitude (Optional)</label>
                    <input type="text" name="company_latitude" value="{{ $company_latitude }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Longitude (Optional)</label>
                    <input type="text" name="company_longitude" value="{{ $company_longitude }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-blue-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>
        
        <div class="admin-form-actions">
            <button type="submit" class="admin-btn-primary">
                Update Company Profile
            </button>
        </div>
    </form>
</div>
@endsection
