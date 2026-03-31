@extends('layouts.admin')

@section('title', 'Edit Coupon: ' . $coupon->code)

@section('content')
<div class="pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900 mb-2">Edit Coupon</h1>
            <p class="text-slate-500 mt-1">Update settings for <strong>{{ $coupon->code }}</strong>.</p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition-all focus-visible:ring-2 focus-visible:ring-slate-300 outline-none">
            ← Back to List
        </a>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8 admin-card">
        @csrf
        @method('PUT')

        <!-- Section 01: Identification -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Coupon Identity
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Coupon Name</label>
                    <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Coupon Code</label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Description (Optional)</label>
                    <textarea name="description" rows="3"
                              class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">{{ old('description', $coupon->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 02: Discount & Rules -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">02</span>
                Discount & Financials
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Discount Type</label>
                    <select name="discount_type" required
                            class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                        <option value="5" {{ old('discount_type', $coupon->discount_type) == 5 ? 'selected' : '' }}>Fixed Amount ($)</option>
                        <option value="10" {{ old('discount_type', $coupon->discount_type) == 10 ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Discount Amount</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', $coupon->discount) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Minimum Order Amount</label>
                    <input type="number" step="0.01" name="minimum_order" value="{{ old('minimum_order', $coupon->minimum_order) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Maximum Discount Amount</label>
                    <input type="number" step="0.01" name="maximum_discount" value="{{ old('maximum_discount', $coupon->maximum_discount) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Limit Per User (Optional)</label>
                    <input type="number" name="limit_per_user" value="{{ old('limit_per_user', $coupon->limit_per_user) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 03: Validity -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">03</span>
                Validity Period
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Start Date & Time</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-amber-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">End Date & Time</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-amber-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 04: Media -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-sm font-bold">04</span>
                Banner Image
            </h2>
            
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="w-full md:w-1/3">
                    <div class="relative group aspect-square rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-pink-300">
                        <img id="preview_image" src="{{ $coupon->image }}" 
                             class="w-full h-full object-cover transition-transform group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs font-bold px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm">Click to Change</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 space-y-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Update Coupon Banner</label>
                    <input type="file" name="image" onchange="previewFile(this)"
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                    <p class="text-xs text-slate-400">Keep empty to maintain current image. Dimensions: 800x800px recommended.</p>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.coupons.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Update Coupon</button>
        </div>
    </form>
</div>

<script>
    function previewFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_image').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
