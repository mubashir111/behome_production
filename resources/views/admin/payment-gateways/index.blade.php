@extends('layouts.admin')

@section('title', 'Payment Gateways')

@section('content')
<div class="max-w-6xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Payment Gateways</h2>
            <p class="text-slate-500 mt-1">Manage and configure your payment providers.</p>
        </div>
    </div>

    @include('admin._alerts')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($paymentGateways as $gateway)
        @php $isActive = $gateway->status == \App\Enums\Activity::ENABLE; @endphp
            <div style="background:#fff; border-radius:20px; border:1px solid {{ $isActive ? '#d1fae5' : '#e2e8f0' }}; box-shadow:0 1px 4px rgba(0,0,0,0.06); overflow:hidden; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.06)'">

                {{-- Top: logo + status badge --}}
                <div style="padding:20px 20px 16px; display:flex; align-items:flex-start; justify-content:space-between;">
                    <div style="width:52px;height:52px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <img src="{{ $gateway->image }}" alt="{{ $gateway->name }}" style="width:32px;height:32px;object-fit:contain;">
                    </div>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;
                        {{ $isActive ? 'background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;' : 'background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;' }}">
                        <span style="width:6px;height:6px;border-radius:50%;display:inline-block;background:{{ $isActive ? '#10b981' : '#94a3b8' }};"></span>
                        {{ $isActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                {{-- Name + slug --}}
                <div style="padding:0 20px 16px;">
                    <div style="font-size:16px;font-weight:700;color:#0f172a;font-family:'Outfit',sans-serif;">{{ $gateway->name }}</div>
                    <div style="font-size:11px;color:#94a3b8;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-top:2px;">{{ $gateway->slug }}</div>
                </div>

                {{-- Toggle row --}}
                <div style="padding:0 20px 16px; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:12px;color:#64748b;font-weight:500;">Gateway Status</span>

                    {{-- Toggle switch form --}}
                    <form id="gw-form-{{ $gateway->id }}" action="{{ route('admin.payment-gateways.update', $gateway->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="payment_type" value="{{ $gateway->slug }}">
                        <input type="hidden" name="gateway_status" value="{{ $isActive ? \App\Enums\Activity::DISABLE : \App\Enums\Activity::ENABLE }}">
                        <button type="button"
                            onclick="confirmSubmit('gw-form-{{ $gateway->id }}', {
                                title: '{{ $isActive ? 'Deactivate Gateway' : 'Activate Gateway' }}',
                                message: '{{ $isActive ? 'Deactivating' : 'Activating' }} {{ addslashes($gateway->name) }} will {{ $isActive ? 'hide it from the checkout. Customers will not be able to use this payment method.' : 'make it available to customers at checkout.' }}',
                                confirmText: '{{ $isActive ? 'Yes, Deactivate' : 'Yes, Activate' }}',
                                type: '{{ $isActive ? 'danger' : 'success' }}'
                            })"
                            title="{{ $isActive ? 'Click to deactivate' : 'Click to activate' }}"
                            style="position:relative;width:44px;height:24px;border-radius:999px;border:none;cursor:pointer;transition:background 0.25s;padding:0;
                                background:{{ $isActive ? '#10b981' : '#cbd5e1' }};">
                            <span style="position:absolute;top:3px;{{ $isActive ? 'right:3px;' : 'left:3px;' }}width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.2);transition:all 0.25s;display:block;"></span>
                        </button>
                    </form>
                </div>

                {{-- Divider + Configure button --}}
                <div style="border-top:1px solid #f1f5f9;padding:14px 20px;">
                    <a href="{{ route('admin.payment-gateways.edit', $gateway->id) }}"
                       style="display:flex;align-items:center;justify-content:center;width:100%;padding:10px 0;border-radius:12px;background:#eef2ff;color:#4f46e5;font-size:13px;font-weight:700;text-decoration:none;transition:background 0.2s,color 0.2s;"
                       onmouseover="this.style.background='#4f46e5';this.style.color='#fff'"
                       onmouseout="this.style.background='#eef2ff';this.style.color='#4f46e5'">
                        Configure Gateway →
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
