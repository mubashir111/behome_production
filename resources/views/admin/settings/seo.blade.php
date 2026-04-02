@extends('layouts.admin')

@section('title', 'SEO Settings')

@section('content')
@php
    $seo_site_title         = old('seo_site_title', data_get($settings, 'seo_site_title', ''));
    $seo_title_separator    = old('seo_title_separator', data_get($settings, 'seo_title_separator', '|'));
    $seo_meta_description   = old('seo_meta_description', data_get($settings, 'seo_meta_description', ''));
    $seo_meta_keywords      = old('seo_meta_keywords', data_get($settings, 'seo_meta_keywords', ''));
    $seo_ga_id              = old('seo_google_analytics_id', data_get($settings, 'seo_google_analytics_id', ''));
    $seo_gtm_id             = old('seo_google_tag_manager_id', data_get($settings, 'seo_google_tag_manager_id', ''));
    $seo_robots             = old('seo_robots_txt', data_get($settings, 'seo_robots_txt', "User-agent: *\nDisallow:"));
    $seo_og_image_url       = \App\Models\ThemeSetting::where('key','seo_og_image')->first()?->getFirstMediaUrl('seo-og-image');
@endphp

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">SEO Settings</h2>
            <p class="admin-page-subtitle">Configure global meta tags, tracking scripts, and search engine directives.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.seo.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Section 01: Meta Defaults -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Default Meta Tags
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Site Title</label>
                    <input type="text" name="seo_site_title" value="{{ $seo_site_title }}"
                           placeholder="e.g. Behome — Luxury Decor"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Default title appended to all page titles.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Title Separator</label>
                    <input type="text" name="seo_title_separator" value="{{ $seo_title_separator }}"
                           placeholder="|"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Character between page title and site title, e.g. <code>|</code> or <code>–</code>.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Meta Description</label>
                    <textarea name="seo_meta_description" rows="3"
                              placeholder="A brief description of your site for search engines…"
                              class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900 resize-none">{{ $seo_meta_description }}</textarea>
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Recommended: 150–160 characters.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Meta Keywords</label>
                    <input type="text" name="seo_meta_keywords" value="{{ $seo_meta_keywords }}"
                           placeholder="luxury furniture, architectural decor, home interiors"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Comma-separated keywords.</p>
                </div>
            </div>
        </div>

        <!-- Section 02: Open Graph / Social Share Image -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">02</span>
                Social Share (Open Graph)
            </h3>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Default OG / Social Share Image</label>
                @if($seo_og_image_url)
                    <div class="mb-3">
                        <img src="{{ $seo_og_image_url }}" alt="OG Image Preview"
                             class="h-40 rounded-xl border-2 border-slate-200 object-cover">
                        <p class="mt-1 text-[10px] text-slate-500 font-medium">Current image — upload a new one to replace it.</p>
                    </div>
                @endif
                <input type="file" name="seo_og_image" accept="image/*"
                       class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-purple-600 outline-none transition-all bg-white text-slate-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                <p class="mt-1 text-[10px] text-slate-500 font-medium">Recommended: 1200×630 px, JPG or PNG. Used when pages are shared on Facebook, Twitter, etc.</p>
            </div>
        </div>

        <!-- Section 03: Tracking -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">03</span>
                Analytics & Tracking
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Google Analytics 4 ID</label>
                    <input type="text" name="seo_google_analytics_id" value="{{ $seo_ga_id }}"
                           placeholder="G-XXXXXXXXXX"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Leave blank to disable GA4.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Google Tag Manager ID</label>
                    <input type="text" name="seo_google_tag_manager_id" value="{{ $seo_gtm_id }}"
                           placeholder="GTM-XXXXXX"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm">
                    <p class="mt-1 text-[10px] text-slate-500 font-medium">Leave blank to disable GTM.</p>
                </div>
            </div>
        </div>

        <!-- Section 04: Robots.txt -->
        <div class="admin-card">
            <h3 class="admin-section-title">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">04</span>
                Robots.txt
            </h3>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">robots.txt Content</label>
                <textarea name="seo_robots_txt" rows="8"
                          class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-amber-600 outline-none transition-all bg-white text-slate-900 font-mono text-sm resize-y">{{ $seo_robots }}</textarea>
                <p class="mt-1 text-[10px] text-slate-500 font-medium">This content will be saved and can be served at <code>/robots.txt</code> via a controller route.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6">
            <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                Save SEO Settings
            </button>
        </div>
    </form>
</div>
@endsection
