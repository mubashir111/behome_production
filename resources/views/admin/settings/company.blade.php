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

    <form action="{{ route('admin.settings.company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Section 00: Logo -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center text-sm font-bold">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                Company Logo
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div>
                    @php $companyLogoModel = \App\Models\ThemeSetting::where('key', 'company_logo')->first(); @endphp
                    <div class="relative group rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-violet-300 p-6 mb-3" style="min-height:120px;">
                        @if($companyLogoModel && $companyLogoModel->company_logo)
                            <img src="{{ $companyLogoModel->company_logo }}" class="h-16 w-auto object-contain" id="preview_company_logo" alt="Company Logo">
                        @else
                            <img src="" class="h-16 w-auto object-contain hidden" id="preview_company_logo" alt="Company Logo">
                            <div class="text-slate-400 text-sm text-center" id="company_logo_placeholder">
                                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                No logo uploaded
                            </div>
                        @endif
                    </div>
                    <input type="file" name="company_logo" accept="image/*"
                           onchange="previewCompanyLogo(this)"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                    <p class="mt-1 text-[10px] text-slate-400">Recommended: 200×60px (PNG/SVG/WebP). Shown in the admin sidebar.</p>
                </div>

                <div class="text-sm text-slate-500 bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <p class="font-semibold text-slate-700 mb-1">Where is this used?</p>
                    <ul class="space-y-1 text-[13px]">
                        <li>• Admin sidebar logo area</li>
                        <li>• Admin panel header/branding</li>
                        <li>• Email templates (if configured)</li>
                    </ul>
                    <p class="mt-3 text-[11px] text-slate-400">For the storefront logo, go to <a href="{{ route('admin.settings.theme') }}" class="text-indigo-600 font-semibold hover:underline">Theme Settings</a>.</p>
                </div>
            </div>
        </div>

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

@push('scripts')
<script>
function previewCompanyLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('preview_company_logo');
            var placeholder = document.getElementById('company_logo_placeholder');
            img.src = e.target.result;
            img.classList.remove('hidden');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
