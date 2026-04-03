@extends('layouts.admin')

@section('title', 'Notification Alerts')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Notification Alerts</h2>
            <p class="admin-page-subtitle">Configure system-wide triggers for order notifications across Email, SMS, and Push.</p>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.settings.notification-alerts.update') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 gap-8">
            @foreach($alerts as $alert)
            <div class="admin-card overflow-hidden">
                <div class="flex items-center justify-between mb-6 pb-4 border-bottom-fade">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-outfit text-lg font-bold text-slate-800">{{ ucwords(str_replace('_', ' ', $alert->language)) }}</h3>
                            <p class="text-xs text-slate-400 font-medium tracking-wide uppercase">{{ $alert->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <!-- Email Toggle -->
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email</span>
                            <label class="custom-switch">
                                <input type="checkbox" name="alerts[{{ $alert->id }}][mail]" value="1" {{ $alert->mail == \App\Enums\SwitchBox::ON ? 'checked' : '' }}>
                                <span class="custom-slider"></span>
                            </label>
                        </div>

                        <!-- SMS Toggle -->
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">SMS</span>
                            <label class="custom-switch">
                                <input type="checkbox" name="alerts[{{ $alert->id }}][sms]" value="1" {{ $alert->sms == \App\Enums\SwitchBox::ON ? 'checked' : '' }}>
                                <span class="custom-slider slider-emerald"></span>
                            </label>
                        </div>

                        <!-- Push Toggle -->
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Push</span>
                            <label class="custom-switch">
                                <input type="checkbox" name="alerts[{{ $alert->id }}][push_notification]" value="1" {{ $alert->push_notification == \App\Enums\SwitchBox::ON ? 'checked' : '' }}>
                                <span class="custom-slider slider-orange"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="admin-label">Email Message Template</label>
                        <textarea name="alerts[{{ $alert->id }}][mail_message]" class="admin-textarea text-xs leading-relaxed" placeholder="Enter email template...">{{ $alert->mail_message }}</textarea>
                    </div>
                    <div>
                        <label class="admin-label">SMS Message Template</label>
                        <textarea name="alerts[{{ $alert->id }}][sms_message]" class="admin-textarea text-xs leading-relaxed" placeholder="Enter SMS template...">{{ $alert->sms_message }}</textarea>
                    </div>
                    <div>
                        <label class="admin-label">Push Notification Template</label>
                        <textarea name="alerts[{{ $alert->id }}][push_notification_message]" class="admin-textarea text-xs leading-relaxed" placeholder="Enter push template...">{{ $alert->push_notification_message }}</textarea>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="admin-form-actions sticky bottom-6 z-10 glass rounded-2xl py-4 px-6 mt-8 shadow-2xl border-indigo-100 flex items-center justify-between">
            <div class="hidden md:flex items-center gap-3 text-slate-500 italic text-[11px] font-medium">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Changes made here affect global system behavior for all users.
            </div>
            <button type="submit" class="admin-btn-primary px-12 py-4">
                Update Global Notification Alerts
            </button>
        </div>
    </form>
</div>

<style>
    .border-bottom-fade {
        border-bottom: 1px solid transparent;
        background: linear-gradient(to right, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%) bottom no-repeat;
        background-size: 100% 1px;
    }
    .admin-textarea {
        background: #fcfdfe !important;
        border: 2px solid #f1f5f9 !important;
        border-radius: 14px !important;
        transition: all 0.2s ease !important;
        width: 100%;
        min-height: 80px;
        padding: 10px;
    }
    .admin-textarea:focus {
        background: #fff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 4px 12px rgba(99,102,241,0.08) !important;
    }

    /* Custom Switch Styles */
    .custom-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .custom-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .custom-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .3s;
        border-radius: 24px;
    }
    .custom-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    input:checked + .custom-slider {
        background-color: #6366f1; /* Indigo / Blue */
    }
    input:checked + .custom-slider.slider-emerald {
        background-color: #10b981;
    }
    input:checked + .custom-slider.slider-orange {
        background-color: #f97316;
    }
    input:checked + .custom-slider:before {
        transform: translateX(20px);
    }
</style>
@endsection
