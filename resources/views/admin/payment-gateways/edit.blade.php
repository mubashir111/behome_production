@extends('layouts.admin')

@section('title', 'Configure ' . $paymentGateway->name)

@section('content')
<div class="admin-panel-container pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold font-outfit text-slate-900">Configure {{ $paymentGateway->name }}</h1>
            <p class="text-slate-500 mt-1">Set up your API credentials and gateway status.</p>
        </div>
        <a href="{{ route('admin.payment-gateways.index') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition-all focus-visible:ring-2 focus-visible:ring-slate-300 outline-none">
            ← Back to List
        </a>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.payment-gateways.update', $paymentGateway->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Dynamic Configuration Fields -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-8 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">01</span>
                Gateway Options
            </h2>
            
            <input type="hidden" name="payment_type" value="{{ $paymentGateway->slug }}">

            @if($paymentGateway->gatewayOptions->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($paymentGateway->gatewayOptions as $option)
                        <div class="space-y-3">
                            <label for="{{ $option->option }}" class="block text-sm font-bold text-slate-700 ml-1">
                                {{ ucwords(str_replace('_', ' ', $option->option)) }}
                            </label>

                            @if($option->type == \App\Enums\InputType::SELECT)
                                @php $activities = json_decode($option->activities, true); @endphp
                                <select name="{{ $option->option }}" id="{{ $option->option }}" required
                                        class="w-full px-5 py-4 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all bg-white text-slate-900">
                                    @foreach($activities as $val => $label)
                                        <option value="{{ $val }}" {{ $option->value == $val ? 'selected' : '' }}>{{ ucfirst($label) }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ str_contains($option->option, 'password') || str_contains($option->option, 'secret') || str_contains($option->option, 'key') ? 'password' : 'text' }}"
                                       name="{{ $option->option }}"
                                       id="{{ $option->option }}"
                                       value="{{ $option->value }}"
                                       required
                                       class="w-full px-5 py-4 rounded-2xl border-2 border-slate-300 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all bg-white text-slate-900">
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                    <p class="text-sm font-semibold text-slate-900">No API credentials are required for this gateway.</p>
                    <p class="mt-2 text-sm text-slate-600">You can manage whether this payment method is available to customers by updating its status below.</p>
                </div>
            @endif
        </div>

        {{-- Status section always visible for all gateways --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-300 shadow-sm">
            <h2 class="text-xl font-bold font-outfit text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    {{ $paymentGateway->gatewayOptions->isNotEmpty() ? '02' : '01' }}
                </span>
                Gateway Status
            </h2>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="gateway_status" value="{{ \App\Enums\Activity::ENABLE }}"
                           {{ $paymentGateway->status == \App\Enums\Activity::ENABLE ? 'checked' : '' }}
                           class="w-4 h-4 accent-emerald-600">
                    <span class="flex items-center gap-2 px-4 py-2 rounded-xl border-2 transition-all
                        {{ $paymentGateway->status == \App\Enums\Activity::ENABLE ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600 group-hover:border-emerald-300' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="font-semibold text-sm">Active</span>
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="gateway_status" value="{{ \App\Enums\Activity::DISABLE }}"
                           {{ $paymentGateway->status == \App\Enums\Activity::DISABLE ? 'checked' : '' }}
                           class="w-4 h-4 accent-slate-500">
                    <span class="flex items-center gap-2 px-4 py-2 rounded-xl border-2 transition-all
                        {{ $paymentGateway->status == \App\Enums\Activity::DISABLE ? 'border-slate-400 bg-slate-100 text-slate-700' : 'border-slate-200 text-slate-500 group-hover:border-slate-400' }}">
                        <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                        <span class="font-semibold text-sm">Inactive</span>
                    </span>
                </label>
            </div>
            <p class="text-xs text-slate-400 mt-3">Inactive gateways will not be shown to customers at checkout.</p>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.payment-gateways.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">Update Configuration</button>
        </div>
    </form>
</div>
@endsection
