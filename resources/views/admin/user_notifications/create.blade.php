@extends('layouts.admin')

@section('title', 'Send Notification')

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Send Notification</h2>
            <p class="admin-page-subtitle">Send a message to a specific customer or broadcast to all users.</p>
        </div>
        <a href="{{ route('admin.user-notifications.index') }}" class="admin-btn-secondary" style="display:inline-flex;align-items:center;gap:8px;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
    </div>

    @include('admin._alerts')

    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        {{-- Form --}}
        <div class="admin-card" style="padding:28px;">
            <form method="POST" action="{{ route('admin.user-notifications.store') }}">
                @csrf

                {{-- Recipient --}}
                <div class="admin-form-group">
                    <label class="admin-form-label">Recipient <span style="color:#ef4444;">*</span></label>
                    <select name="user_id" class="admin-form-select @error('user_id') is-invalid @enderror">
                        <option value="">📢 All Users (Broadcast)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="admin-form-error">{{ $message }}</p>@enderror
                    <p class="admin-form-hint">Leave blank to send to all registered users.</p>
                </div>

                {{-- Title --}}
                <div class="admin-form-group">
                    <label class="admin-form-label">Title <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" id="titleInput"
                           class="admin-form-input @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="e.g. Special Offer Just For You!" maxlength="200">
                    @error('title')<p class="admin-form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Body --}}
                <div class="admin-form-group">
                    <label class="admin-form-label">Message <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
                    <textarea name="body" id="bodyInput"
                              class="admin-form-input @error('body') is-invalid @enderror"
                              rows="3" placeholder="Optional details shown below the title…" maxlength="500">{{ old('body') }}</textarea>
                    @error('body')<p class="admin-form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Type / Icon / Color --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Type <span style="color:#ef4444;">*</span></label>
                        <select name="type" class="admin-form-select">
                            <option value="info"    {{ old('type','info') === 'info'    ? 'selected' : '' }}>ℹ️ Info</option>
                            <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>✅ Success</option>
                            <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>⚠️ Warning</option>
                            <option value="promo"   {{ old('type') === 'promo'   ? 'selected' : '' }}>🎁 Promo</option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Icon</label>
                        <select name="icon" class="admin-form-select">
                            <option value="bell"    {{ old('icon','bell') === 'bell'    ? 'selected' : '' }}>🔔 Bell</option>
                            <option value="gift"    {{ old('icon') === 'gift'    ? 'selected' : '' }}>🎁 Gift</option>
                            <option value="check"   {{ old('icon') === 'check'   ? 'selected' : '' }}>✅ Check</option>
                            <option value="warning" {{ old('icon') === 'warning' ? 'selected' : '' }}>⚠️ Warning</option>
                            <option value="truck"   {{ old('icon') === 'truck'   ? 'selected' : '' }}>🚚 Truck</option>
                            <option value="return"  {{ old('icon') === 'return'  ? 'selected' : '' }}>↩️ Return</option>
                            <option value="x"       {{ old('icon') === 'x'       ? 'selected' : '' }}>❌ Cancel</option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Color</label>
                        <select name="color" class="admin-form-select" id="colorSelect">
                            <option value="#6366f1" {{ old('color','#6366f1') === '#6366f1' ? 'selected' : '' }}>🟣 Indigo</option>
                            <option value="#10b981" {{ old('color') === '#10b981' ? 'selected' : '' }}>🟢 Green</option>
                            <option value="#f59e0b" {{ old('color') === '#f59e0b' ? 'selected' : '' }}>🟡 Amber</option>
                            <option value="#ef4444" {{ old('color') === '#ef4444' ? 'selected' : '' }}>🔴 Red</option>
                            <option value="#3b82f6" {{ old('color') === '#3b82f6' ? 'selected' : '' }}>🔵 Blue</option>
                            <option value="#d4b062" {{ old('color') === '#d4b062' ? 'selected' : '' }}>🟤 Gold</option>
                        </select>
                    </div>
                </div>

                {{-- Link --}}
                <div class="admin-form-group">
                    <label class="admin-form-label">Link <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
                    <input type="text" name="link" class="admin-form-input @error('link') is-invalid @enderror"
                           value="{{ old('link') }}" placeholder="/shop or /account">
                    @error('link')<p class="admin-form-error">{{ $message }}</p>@enderror
                    <p class="admin-form-hint">Where tapping the notification takes the customer.</p>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="admin-btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Notification
                    </button>
                    <a href="{{ route('admin.user-notifications.index') }}" class="admin-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        {{-- Live Preview --}}
        <div>
            <div class="admin-card" style="padding:20px;">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 14px;">Live Preview</p>
                <div style="background:#1a1a2e;border-radius:12px;padding:14px;display:flex;gap:10px;">
                    <div id="prev-icon" style="width:36px;height:36px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:#6366f120;color:#6366f1;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p id="prev-title" style="color:#fff;font-size:13px;font-weight:700;margin:0 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Notification title</p>
                        <p id="prev-body"  style="color:rgba(255,255,255,0.5);font-size:12px;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Message body text</p>
                        <p style="color:rgba(255,255,255,0.3);font-size:10px;margin:0;">just now</p>
                    </div>
                </div>
                <p style="font-size:12px;color:#94a3b8;margin:12px 0 0;">This is how it appears in the customer's notification bell.</p>
            </div>

            {{-- Tips --}}
            <div class="admin-card" style="padding:20px;margin-top:16px;">
                <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:0 0 12px;">Tips</p>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    <li style="font-size:12px;color:#64748b;display:flex;gap:8px;"><span style="color:#10b981;">✓</span> Keep titles under 40 characters</li>
                    <li style="font-size:12px;color:#64748b;display:flex;gap:8px;"><span style="color:#10b981;">✓</span> Add a link to drive action</li>
                    <li style="font-size:12px;color:#64748b;display:flex;gap:8px;"><span style="color:#10b981;">✓</span> Use Broadcast for promotions</li>
                    <li style="font-size:12px;color:#64748b;display:flex;gap:8px;"><span style="color:#10b981;">✓</span> Target a user for personal updates</li>
                </ul>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    const titleInput = document.getElementById('titleInput');
    const bodyInput  = document.getElementById('bodyInput');
    const colorSel   = document.getElementById('colorSelect');
    const prevTitle  = document.getElementById('prev-title');
    const prevBody   = document.getElementById('prev-body');
    const prevIcon   = document.getElementById('prev-icon');

    function updatePreview() {
        prevTitle.textContent = titleInput.value || 'Notification title';
        prevBody.textContent  = bodyInput.value  || 'Message body text';
        const c = colorSel.value;
        prevIcon.style.background = c + '20';
        prevIcon.style.color      = c;
    }
    titleInput.addEventListener('input',  updatePreview);
    bodyInput.addEventListener('input',   updatePreview);
    colorSel.addEventListener('change',   updatePreview);
</script>
@endpush
@endsection
