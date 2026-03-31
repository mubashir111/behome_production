@extends('layouts.admin')
@section('title', 'Edit ' . $page->title)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Edit: {{ $page->title }}</h2>
        <p class="text-slate-500 mt-1 text-sm font-mono">/{{ $page->slug }}</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="admin-btn-secondary">← Back</a>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data" style="width:100%">
@csrf
@method('PUT')

{{-- Common meta fields --}}
<div class="admin-card mb-6">
    <div class="admin-card-header"><h3 class="admin-card-title">Page Settings</h3></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Page Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                <span class="text-sm font-semibold text-slate-700">Page is Active (visible on frontend)</span>
            </label>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Meta Description</label>
            <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>
</div>

{{-- About page specific form --}}
@if($page->slug === 'about')
    @php $s = $page->sections ?? []; @endphp

    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Hero Section</h3></div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hero Title</label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $s['hero']['title'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hero Subtitle</label>
                <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $s['hero']['subtitle'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hero Description</label>
                <textarea name="hero_description" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('hero_description', $s['hero']['description'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="admin-card mb-6">
        <div class="admin-card-header flex items-center justify-between">
            <h3 class="admin-card-title">Feature Timeline</h3>
            <button type="button" onclick="addFeatureRow()" class="admin-btn-secondary text-xs">+ Add Feature</button>
        </div>
        <div id="features-container" class="space-y-3">
            @foreach(old('feature_title', array_column($s['features'] ?? [], 'title')) as $i => $title)
            <div class="feature-row grid grid-cols-12 gap-2 items-center bg-slate-50 p-3 rounded-lg">
                <div class="col-span-1"><label class="text-xs text-slate-500">No.</label><input type="text" name="feature_number[]" value="{{ old('feature_number.' . $i, ($s['features'][$i]['number'] ?? '0' . ($i+1))) }}" class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs"></div>
                <div class="col-span-1"><label class="text-xs text-slate-500">Year</label><input type="text" name="feature_year[]" value="{{ old('feature_year.' . $i, ($s['features'][$i]['year'] ?? '')) }}" class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs"></div>
                <div class="col-span-3"><label class="text-xs text-slate-500">Title</label><input type="text" name="feature_title[]" value="{{ $title }}" class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs"></div>
                <div class="col-span-6"><label class="text-xs text-slate-500">Description</label><input type="text" name="feature_description[]" value="{{ old('feature_description.' . $i, ($s['features'][$i]['description'] ?? '')) }}" class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs"></div>
                <div class="col-span-1 pt-4"><button type="button" onclick="this.closest('.feature-row').remove()" class="text-rose-400 hover:text-rose-600 text-xs">✕</button></div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card mb-6">
        <div class="admin-card-header flex items-center justify-between">
            <h3 class="admin-card-title">Key Stats</h3>
            <button type="button" onclick="addStatRow()" class="admin-btn-secondary text-xs">+ Add Stat</button>
        </div>
        <div id="stats-container" class="space-y-2">
            @foreach(old('stat_label', array_column($s['stats'] ?? [], 'label')) as $i => $label)
            <div class="stat-row flex gap-3 items-center">
                <input type="text" name="stat_value[]" value="{{ old('stat_value.' . $i, ($s['stats'][$i]['value'] ?? '')) }}" placeholder="Value (e.g. 10,000+)" class="w-32 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="text" name="stat_label[]" value="{{ $label }}" placeholder="Label (e.g. Happy Customers)" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="button" onclick="this.closest('.stat-row').remove()" class="text-rose-400 hover:text-rose-600">✕</button>
            </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card mb-6">
        <div class=\"admin-card-header flex items-center justify-between\">
            <h3 class=\"admin-card-title\">Team Members</h3>
            <button type=\"button\" id=\"add-team-btn\" class=\"admin-btn-secondary text-xs\">+ Add Member</button>
        </div>
        <div id=\"team-container\" class=\"space-y-3\">
            @foreach(old('team_name', array_column($s['team'] ?? [], 'name')) as $i => $name)
            @php $existingImage = old('team_image.' . $i, ($s['team'][$i]['image'] ?? '')); @endphp
            <div class=\"team-row bg-slate-50 border border-slate-200 rounded-2xl p-4\">
                <div class=\"flex gap-3 mb-4 items-end\">
                    <div class=\"flex-1\">
                        <label class=\"block text-xs font-semibold text-slate-600 mb-1.5\">Name</label>
                        <input type=\"text\" name=\"team_name[]\" value=\"{{ $name }}\" class=\"w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500\">
                    </div>
                    <div class=\"flex-1\">
                        <label class=\"block text-xs font-semibold text-slate-600 mb-1.5\">Role</label>
                        <input type=\"text\" name=\"team_role[]\" value=\"{{ old('team_role.' . $i, ($s['team'][$i]['role'] ?? '')) }}\" class=\"w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500\">
                    </div>
                    <button type=\"button\" class=\"remove-team-btn text-rose-500 hover:text-rose-700 font-bold text-lg leading-none flex-shrink-0\" title=\"Remove\">✕</button>
                </div>
                <div class=\"flex gap-4 items-start\">
                    <div class=\"flex-shrink-0\">
                        <label class=\"block text-xs font-semibold text-slate-600 mb-1.5\">Photo</label>
                        <img class=\"team-preview w-20 h-20 object-cover rounded-lg border border-slate-200 mb-2\" src=\"{{ $existingImage }}\" style=\"display:{{ $existingImage ? 'block' : 'none' }}\">
                        <input type=\"hidden\" name=\"team_image[]\" value=\"{{ $existingImage }}\">
                        <input type=\"file\" name=\"team_image_file[]\" accept=\"image/*\" class=\"team-image-file text-xs cursor-pointer max-w-xs\">
                    </div>
                    <div class=\"flex-1\">
                        <label class=\"block text-xs font-semibold text-slate-600 mb-1.5\">Description</label>
                        <textarea name=\"team_description[]\" rows=\"3\" class=\"w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500\">{{ old('team_description.' . $i, ($s['team'][$i]['description'] ?? '')) }}</textarea>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Template for new member rows (not submitted with form) --}}
    <template id="team-row-template">
        <div class="team-row bg-slate-50 border border-slate-200 rounded-2xl p-4">
            <div class="flex gap-3 mb-4 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Name</label>
                    <input type="text" name="team_name[]" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Role</label>
                    <input type="text" name="team_role[]" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" class="remove-team-btn text-rose-500 hover:text-rose-700 font-bold text-lg leading-none flex-shrink-0" title="Remove">✕</button>
            </div>
            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Photo</label>
                    <img class="team-preview w-20 h-20 object-cover rounded-lg border border-slate-200 mb-2" style="display:none;">
                    <input type="hidden" name="team_image[]" value="">
                    <input type="file" name="team_image_file[]" accept="image/*" class="team-image-file text-xs cursor-pointer max-w-xs">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Description</label>
                    <textarea name="team_description[]" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>
        </div>
    </template>

{{-- Contact page specific form --}}
@elseif($page->slug === 'contact')
    @php $s = $page->sections ?? []; @endphp

    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Contact Information</h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Office Address</label>
                <input type="text" name="address" value="{{ old('address', $s['address'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone Numbers <span class="text-xs text-slate-400">(one per line)</span></label>
                <textarea name="phones" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('phones', implode("\n", $s['phones'] ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Addresses <span class="text-xs text-slate-400">(one per line)</span></label>
                <textarea name="emails" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('emails', implode("\n", $s['emails'] ?? [])) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Careers Email</label>
                <input type="email" name="careers_email" value="{{ old('careers_email', $s['careers_email'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Map Search Query</label>
                <input type="text" name="map_query" value="{{ old('map_query', $s['map_query'] ?? '') }}" placeholder="e.g. Westminster, London" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

{{-- Generic page with rich text --}}
@else
    <div class="admin-card mb-6">
        <div class="admin-card-header"><h3 class="admin-card-title">Page Content</h3></div>
        <textarea name="content" id="content-editor" rows="20" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $page->content) }}</textarea>
        <p class="text-xs text-slate-400 mt-1">You can use HTML for formatting.</p>
    </div>
@endif

<div class="flex items-center gap-3">
    <button type="submit" class="admin-btn-primary px-6 py-2.5">Save Changes</button>
    <a href="{{ route('admin.pages.index') }}" class="admin-btn-secondary px-6 py-2.5">Cancel</a>
</div>

</form>

<style>
.feature-row, .team-row { transition: background 0.15s; }
.feature-row:hover, .team-row:hover { background: #eef2ff; }
</style>

<script>
(function() {
    // ── Add team member ──────────────────────────────────────────
    var addBtn = document.getElementById('add-team-btn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            var tmpl = document.getElementById('team-row-template');
            var clone = document.importNode(tmpl.content, true);
            clone.querySelector('.team-image-file').addEventListener('change', function() {
                previewTeamImage(this);
            });
            document.getElementById('team-container').appendChild(clone);
        });
    }

    // ── Remove team member (event delegation) ────────────────────
    var container = document.getElementById('team-container');
    if (container) {
        container.addEventListener('click', function(e) {
            var btn = e.target.closest('.remove-team-btn');
            if (btn) btn.closest('.team-row').remove();
        });
        // Wire up file inputs on existing rows
        container.querySelectorAll('.team-image-file').forEach(function(inp) {
            inp.addEventListener('change', function() { previewTeamImage(this); });
        });
    }

    // ── Image preview ─────────────────────────────────────────────
    function previewTeamImage(input) {
        if (!input.files || !input.files[0]) return;
        var row = input.closest('.team-row');
        var preview = row.querySelector('.team-preview');
        if (!preview) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }

    // ── Add feature row ───────────────────────────────────────────
    var addFeatureBtn = document.querySelector('[onclick="addFeatureRow()"]');
    if (addFeatureBtn) {
        addFeatureBtn.removeAttribute('onclick');
        addFeatureBtn.addEventListener('click', function() {
            var c = document.getElementById('features-container');
            var i = c.children.length;
            var row = document.createElement('div');
            row.className = 'feature-row';
            row.style.cssText = 'display:flex;gap:8px;align-items:flex-end;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;';
            row.innerHTML = '<div style="width:60px"><label style="display:block;font-size:11px;color:#64748b;margin-bottom:3px;">No.</label><input type="text" name="feature_number[]" placeholder="0' + (i+1) + '" style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:12px;"></div>'
                + '<div style="width:70px"><label style="display:block;font-size:11px;color:#64748b;margin-bottom:3px;">Year</label><input type="text" name="feature_year[]" placeholder="2024" style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:12px;"></div>'
                + '<div style="flex:1"><label style="display:block;font-size:11px;color:#64748b;margin-bottom:3px;">Title</label><input type="text" name="feature_title[]" style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:12px;"></div>'
                + '<div style="flex:2"><label style="display:block;font-size:11px;color:#64748b;margin-bottom:3px;">Description</label><input type="text" name="feature_description[]" style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:4px 6px;font-size:12px;"></div>'
                + '<button type="button" style="color:#f87171;font-size:18px;font-weight:700;border:none;background:transparent;cursor:pointer;flex-shrink:0;">✕</button>';
            row.querySelector('button').addEventListener('click', function() { row.remove(); });
            c.appendChild(row);
        });
    }

    // ── Add stat row ──────────────────────────────────────────────
    var addStatBtn = document.querySelector('[onclick="addStatRow()"]');
    if (addStatBtn) {
        addStatBtn.removeAttribute('onclick');
        addStatBtn.addEventListener('click', function() {
            var c = document.getElementById('stats-container');
            var row = document.createElement('div');
            row.className = 'stat-row';
            row.style.cssText = 'display:flex;gap:12px;align-items:center;';
            row.innerHTML = '<input type="text" name="stat_value[]" placeholder="Value (e.g. 10,000+)" style="width:160px;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:14px;">'
                + '<input type="text" name="stat_label[]" placeholder="Label (e.g. Happy Customers)" style="flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:14px;">'
                + '<button type="button" style="color:#f87171;font-size:18px;font-weight:700;border:none;background:transparent;cursor:pointer;">✕</button>';
            row.querySelector('button').addEventListener('click', function() { row.remove(); });
            c.appendChild(row);
        });
    }
})();
</script>

@endsection
