@extends('layouts.admin')

@section('title', 'Customer Profile')

@section('content')
<div class="pb-10">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Customer: {{ $customer->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage customer profile, addresses, and account.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.index') }}" class="admin-btn-secondary">
                ← Back to Customers
            </a>
        </div>
    </div>

    @include('admin._alerts')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Profile & Contact -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="admin-card">
                <div class="admin-card-header text-center">
                    <h2 class="admin-card-title">Profile</h2>
                </div>
                
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold mb-4">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $customer->name }}</h2>
                    <p class="text-slate-600 text-sm">{{ $customer->email }}</p>
                    
                    <div class="w-full mt-4 pt-4 border-t border-slate-200">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-lg">
                                <p class="text-xs text-slate-500 font-semibold">Status</p>
                                @if($customer->status == 1)
                                    <span class="inline-block mt-1 px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded">Active</span>
                                @else
                                    <span class="inline-block mt-1 px-2 py-1 bg-rose-100 text-rose-700 text-xs font-bold rounded">Inactive</span>
                                @endif
                            </div>
                            <div class="p-3 bg-slate-50 rounded-lg">
                                <p class="text-xs text-slate-500 font-semibold">Type</p>
                                <p class="text-sm font-bold text-slate-900 mt-1">Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Contact Information</h2>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Email</p>
                        <p class="font-semibold text-slate-900">{{ $customer->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Phone</p>
                        <p class="font-semibold text-slate-900">{{ $customer->phone ?: 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-semibold uppercase">Joined</p>
                        <p class="font-semibold text-slate-900">{{ $customer->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Addresses & Actions -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Saved Addresses -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Saved Addresses</h2>
                    <p class="admin-card-subtitle">{{ $customer->addresses->count() }} address(es) on file</p>
                </div>
                
                @if($customer->addresses->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($customer->addresses as $address)
                        <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 hover:border-indigo-300 transition-colors">
                            <div class="flex items-start justify-between mb-2">
                                <p class="text-xs font-semibold text-slate-600 uppercase">{{ $address->label ?: 'Address' }}</p>
                            </div>
                            <p class="font-semibold text-slate-900">{{ $address->full_name }}</p>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                {{ $address->address }}<br>
                                {{ $address->city }}, {{ $address->state }} {{ $address->zip_code }}<br>
                                {{ $address->country }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-slate-500 italic py-8">No addresses saved yet.</p>
                @endif
            </div>

            <!-- Danger Zone -->
            <div class="admin-card border-rose-200 bg-rose-50/30">
                <div class="admin-card-header">
                    <h2 class="admin-card-title text-rose-600">Danger Zone</h2>
                    <p class="admin-card-subtitle">Deleting account will permanently remove all customer data.</p>
                </div>
                
                <button type="button" onclick="confirmSubmit('del-customer-{{ $customer->id }}', { title: 'Delete Customer Account', message: 'Are you absolutely sure? This will permanently delete all customer data and cannot be undone.', confirmText: 'Yes, Delete Account', type: 'danger' })" class="px-6 py-2 bg-rose-500 text-white rounded-lg font-semibold hover:bg-rose-600 transition-colors">
                    Delete Customer Account
                </button>
                <form id="del-customer-{{ $customer->id }}" action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
