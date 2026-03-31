@extends('layouts.admin')

@section('title', 'Theme Settings')

@section('content')
<div class="max-w-5xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Theme Settings</h2>
            <p class="text-slate-500 mt-1">Customize your application's branding and appearance.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.theme.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Section 01: Logo Management -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h3 class="text-xl font-bold font-outfit text-slate-900 mb-8 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Brand Assets
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Main Logo -->
                <div class="space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Main Logo</label>
                    <div class="relative group aspect-video rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-indigo-300">
                        @php $logo = \App\Models\ThemeSetting::where('key', 'theme_logo')->first(); @endphp
                        <img src="{{ $logo ? $logo->logo : asset('images/default/theme-logo.png') }}" 
                             class="h-16 w-auto object-contain transition-transform group-hover:scale-105" id="preview_logo">
                    </div>
                    <input type="file" name="theme_logo" onchange="previewImage(this, 'preview_logo')"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-[10px] text-slate-400">Recommended: 200x60px (PNG/JPG)</p>
                </div>

                <!-- Favicon -->
                <div class="space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Favicon</label>
                    <div class="relative group aspect-square w-24 mx-auto rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-emerald-300">
                        @php $favicon = \App\Models\ThemeSetting::where('key', 'theme_favicon_logo')->first(); @endphp
                        <img src="{{ $favicon ? $favicon->favicon_logo : asset('images/default/theme-favicon-logo.png') }}" 
                             class="w-12 h-12 object-contain transition-transform group-hover:scale-105" id="preview_favicon">
                    </div>
                    <input type="file" name="theme_favicon_logo" onchange="previewImage(this, 'preview_favicon')"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-[10px] text-slate-400 text-center">Recommended: 32x32px (PNG/ICO)</p>
                </div>

                <!-- Footer Logo -->
                <div class="space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Footer Logo</label>
                    <div class="relative group aspect-video rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-purple-300">
                        @php $footer = \App\Models\ThemeSetting::where('key', 'theme_footer_logo')->first(); @endphp
                        <img src="{{ $footer ? $footer->footer_logo : asset('images/default/theme-footer-logo.png') }}" 
                             class="h-16 w-auto object-contain transition-transform group-hover:scale-105" id="preview_footer">
                    </div>
                    <input type="file" name="theme_footer_logo" onchange="previewImage(this, 'preview_footer')"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    <p class="text-[10px] text-slate-400">Recommended: 200x60px (PNG/JPG)</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6">
            <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 transform hover:-translate-y-1">
                Save Theme Assets
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
