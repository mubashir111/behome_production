@extends('layouts.admin')

@section('title', 'Edit Supplier: ' . $supplier->name)

@section('content')
<div class="pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900 mb-2">Edit Supplier</h1>
            <p class="text-slate-500 mt-1">Update information for <strong>{{ $supplier->company }}</strong>.</p>
        </div>
        <a href="{{ route('admin.suppliers.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition-all focus-visible:ring-2 focus-visible:ring-slate-300 outline-none">
            ← Back to List
        </a>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8 admin-card">
        @csrf
        @method('PUT')

        <!-- Section 01: Company Info -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Company Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Company Name</label>
                    <input type="text" name="company" value="{{ old('company', $supplier->company) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Contact Person Name</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div class="flex gap-4">
                    <div class="w-1/3">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Code</label>
                        <input type="text" name="country_code" value="{{ old('country_code', $supplier->country_code) }}"
                               class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                               class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 02: Location -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">02</span>
                Location & Address
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Address</label>
                    <textarea name="address" rows="2"
                              class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">{{ old('address', $supplier->address) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">City</label>
                    <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">State / Province</label>
                    <input type="text" name="state" value="{{ old('state', $supplier->state) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Country</label>
                    <input type="text" name="country" value="{{ old('country', $supplier->country) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Zip / Postal Code</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $supplier->zip_code) }}"
                           class="w-full px-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 outline-none transition-all bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- Section 03: Profile Media -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center text-sm font-bold">03</span>
                Supplier Profile Image
            </h2>
            
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="w-full md:w-1/3">
                    <div class="relative group aspect-square rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all hover:border-pink-300">
                        <img id="preview_image" src="{{ $supplier->image }}" 
                             class="w-full h-full object-cover transition-transform group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs font-bold px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm">Click to Change</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 space-y-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Update Profile Image</label>
                    <input type="file" name="image" onchange="previewFile(this)"
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                    <p class="text-xs text-slate-400">Keep empty to maintain current image. Dimensions: 800x800px recommended.</p>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.suppliers.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Update Supplier</button>
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
