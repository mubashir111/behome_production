@extends('layouts.admin')

@section('title', 'Shipping Settings')

@section('content')
@php
    $method = old('shipping_setup_method', data_get($settings, 'shipping_setup_method', 5));
    $flat_cost = old('shipping_setup_flat_rate_wise_cost', data_get($settings, 'shipping_setup_flat_rate_wise_cost', 0));
    $area_cost = old('shipping_setup_area_wise_default_cost', data_get($settings, 'shipping_setup_area_wise_default_cost', 0));
@endphp

<div class="admin-panel-container pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Shipping Settings</h2>
            <p class="text-slate-500 mt-1">Configure your delivery methods and shipping costs.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Section 01: Shipping Method -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h3 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Delivery Configuration
            </h3>
            
            <div class="space-y-8">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-4">Select Shipping Method</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Product Wise -->
                        <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $method == 5 ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" name="shipping_setup_method" value="5" {{ $method == 5 ? 'checked' : '' }} class="absolute opacity-0" onchange="toggleShippingFields(5)">
                            <span class="text-sm font-bold {{ $method == 5 ? 'text-indigo-900' : 'text-slate-700' }}">Product Wise</span>
                            <span class="text-[10px] {{ $method == 5 ? 'text-indigo-600' : 'text-slate-400' }} mt-1">Cost defined per product</span>
                        </label>

                        <!-- Flat Wise -->
                        <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $method == 10 ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" name="shipping_setup_method" value="10" {{ $method == 10 ? 'checked' : '' }} class="absolute opacity-0" onchange="toggleShippingFields(10)">
                            <span class="text-sm font-bold {{ $method == 10 ? 'text-indigo-900' : 'text-slate-700' }}">Flat Rate</span>
                            <span class="text-[10px] {{ $method == 10 ? 'text-indigo-600' : 'text-slate-400' }} mt-1">One price for all orders</span>
                        </label>

                        <!-- Area Wise -->
                        <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $method == 15 ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" name="shipping_setup_method" value="15" {{ $method == 15 ? 'checked' : '' }} class="absolute opacity-0" onchange="toggleShippingFields(15)">
                            <span class="text-sm font-bold {{ $method == 15 ? 'text-indigo-900' : 'text-slate-700' }}">Area Wise</span>
                            <span class="text-[10px] {{ $method == 15 ? 'text-indigo-600' : 'text-slate-400' }} mt-1">Cost based on delivery area</span>
                        </label>
                    </div>
                </div>

                <!-- Flat Rate Details -->
                <div id="flat_rate_fields" class="{{ $method == 10 ? '' : 'hidden' }} space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Flat Shipping Cost</label>
                    <div class="relative max-w-xs">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" name="shipping_setup_flat_rate_wise_cost" value="{{ $flat_cost }}"
                               class="w-full pl-10 pr-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    </div>
                </div>

                <!-- Area Wise Details -->
                <div id="area_wise_fields" class="{{ $method == 15 ? '' : 'hidden' }} space-y-4">
                    <label class="block text-sm font-bold text-slate-700">Default Area Shipping Cost</label>
                    <div class="relative max-w-xs">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" name="shipping_setup_area_wise_default_cost" value="{{ $area_cost }}"
                               class="w-full pl-10 pr-5 py-3 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 outline-none transition-all bg-white text-slate-900">
                    </div>
                    <p class="text-xs text-slate-500">Note: You can override this for specific areas in <a href="{{ route('admin.shipping.order-areas') }}" class="text-indigo-600 font-bold hover:underline">Delivery Areas</a> settings.</p>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn-primary">Save Shipping Settings</button>
        </div>
    </form>
</div>

<script>
    function toggleShippingFields(method) {
        const flatFields = document.getElementById('flat_rate_fields');
        const areaFields = document.getElementById('area_wise_fields');
        
        // Reset highlights
        document.querySelectorAll('label.relative').forEach(el => {
            el.classList.remove('border-indigo-600', 'bg-indigo-50');
            el.classList.add('border-slate-200', 'bg-white');
            el.querySelector('span.text-sm').classList.remove('text-indigo-900');
            el.querySelector('span.text-sm').classList.add('text-slate-700');
        });

        // Highlight selected
        const selectedLabel = event.target.closest('label');
        selectedLabel.classList.add('border-indigo-600', 'bg-indigo-50');
        selectedLabel.classList.remove('border-slate-200', 'bg-white');
        selectedLabel.querySelector('span.text-sm').classList.add('text-indigo-900');
        selectedLabel.querySelector('span.text-sm').classList.remove('text-slate-700');

        if (method === 10) {
            flatFields.classList.remove('hidden');
            areaFields.classList.add('hidden');
        } else if (method === 15) {
            flatFields.classList.add('hidden');
            areaFields.classList.remove('hidden');
        } else {
            flatFields.classList.add('hidden');
            areaFields.classList.add('hidden');
        }
    }
</script>
@endsection
