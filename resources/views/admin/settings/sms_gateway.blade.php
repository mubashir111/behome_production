@extends('layouts.admin')

@section('title', 'SMS Gateway')

@section('content')
<div class="admin-page text-slate-800">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">SMS Gateway Integration</h2>
            <p class="admin-page-subtitle">Configure and manage your third-party SMS providers and API credentials.</p>
        </div>
    </div>

    @include('admin._alerts')

    <div class="grid grid-cols-1 gap-8">
        @foreach($gateways as $gateway)
        <div class="admin-card overflow-hidden transition-all duration-300 hover:shadow-xl border-2 {{ $gateway->status == 1 ? 'border-indigo-100' : 'border-slate-100 opacity-80' }}">
            <div class="flex flex-wrap items-center justify-between gap-6 mb-8 pb-6 border-b border-slate-50">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl {{ $gateway->status == 1 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shadow-lg">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="font-outfit text-xl font-extrabold tracking-tight">{{ $gateway->name }}</h3>
                            @if($gateway->status == 1)
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-widest border border-emerald-200">Active</span>
                            @else
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-widest border border-slate-200">Disabled</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-400 mt-1">Provider ID: <code class="bg-slate-50 px-2 py-0.5 rounded text-indigo-400 font-mono text-xs">{{ $gateway->slug }}</code></p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 p-2 rounded-2xl border border-slate-100">
                    <span class="text-xs font-bold text-slate-500 px-3">Gateway Status</span>
                    <form action="{{ route('admin.settings.sms-gateway.update') }}" method="POST" id="form-status-{{ $gateway->id }}">
                        @csrf
                        <input type="hidden" name="sms_type" value="{{ $gateway->slug }}">
                        @foreach($gateway->gatewayOptions as $option)
                            @if(str_contains($option->option, 'status'))
                                <input type="hidden" name="{{ $option->option }}" value="{{ $gateway->status == 1 ? 5 : 1 }}"> {{-- Toggle --}}
                            @else
                                <input type="hidden" name="{{ $option->option }}" value="{{ $option->value }}">
                            @endif
                        @endforeach
                        <button type="submit" class="admin-btn-sm {{ $gateway->status == 1 ? 'admin-btn-secondary !text-slate-400' : 'admin-btn-primary' }}">
                            {{ $gateway->status == 1 ? 'Deactivate' : 'Activate Gateway' }}
                        </button>
                    </form>
                </div>
            </div>

            <form action="{{ route('admin.settings.sms-gateway.update') }}" method="POST">
                @csrf
                <input type="hidden" name="sms_type" value="{{ $gateway->slug }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6 bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                    @foreach($gateway->gatewayOptions as $option)
                    <div class="admin-form-group mb-0">
                        <label class="admin-label text-slate-500 uppercase tracking-widest text-[10px] font-extrabold mb-2">{{ ucwords(str_replace('_', ' ', str_replace($gateway->slug . '_', '', $option->option))) }}</label>
                        @if(str_contains($option->option, 'status'))
                            <select name="{{ $option->option }}" class="admin-select !bg-white">
                                <option value="1" {{ $option->value == 1 ? 'selected' : '' }}>Active</option>
                                <option value="5" {{ $option->value == 5 ? 'selected' : '' }}>Disabled</option>
                            </select>
                        @else
                            <input type="{{ str_contains($option->option, 'secret') || str_contains($option->option, 'token') ? 'password' : 'text' }}" 
                                   name="{{ $option->option }}" 
                                   value="{{ $option->value }}" 
                                   class="admin-input !bg-white focus:ring-4 ring-indigo-50"
                                   placeholder="Enter {{ str_replace('_', ' ', $option->option) }}...">
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="admin-form-actions mt-8 justify-between items-center">
                    <div class="text-[11px] text-slate-400 font-medium italic flex items-center gap-2">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ensure your API credentials matched exactly as provided by {{ $gateway->name }}.
                    </div>
                    <button type="submit" class="admin-btn-primary px-10 py-3.5 shadow-indigo-200">Save {{ $gateway->name }} Config</button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
