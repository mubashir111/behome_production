{{--
    Image Uploader with Cropper Partial
    Required variables:
      $inputName      - form field name, e.g. 'image', 'logo', 'cover_image'
      $label          - human label, e.g. 'Banner Image'
    Optional variables:
      $aspectRatio    - float (1 = square, 1.905 = 16:9) or null for free crop. Default: null
      $currentImageUrl - existing image URL string. Default: null
      $hint           - helper text below the widget. Default: null
      $required       - bool, marks field required when no existing image. Default: false
      $outputWidth    - cropped canvas width in px. Default: 800
      $outputHeight   - cropped canvas height in px. Default: 800
--}}
@php
    $uid     = 'ciu-' . str_replace(['_', '[', ']'], '-', $inputName);
    $ratio   = isset($aspectRatio) && $aspectRatio !== null ? (float)$aspectRatio : null;
    $jsRatio = $ratio !== null ? $ratio : 'NaN';
    $outW    = $outputWidth  ?? 800;
    $outH    = $outputHeight ?? 800;
    $hasCur  = !empty($currentImageUrl);
@endphp

@push('styles')
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
@endonce
@endpush

<div class="image-uploader-widget" id="{{ $uid }}-widget">
    {{-- Current / preview image --}}
    <div id="{{ $uid }}-preview-container" class="{{ $hasCur ? '' : 'hidden' }} mb-3">
        <p class="text-xs text-slate-500 mb-2">{{ $hasCur ? 'Current Image' : 'Selected Image' }}</p>
        <img id="{{ $uid }}-preview" src="{{ $currentImageUrl ?? '' }}" alt=""
             class="h-36 w-full object-cover rounded-xl border border-slate-200">
        <button type="button" onclick="ciuReset('{{ $uid }}')"
                class="mt-2 text-xs font-bold text-rose-600 hover:text-rose-700 uppercase tracking-wide">
            ✕ Remove / Change Image
        </button>
    </div>

    {{-- Drop / upload trigger --}}
    <label id="{{ $uid }}-upload-label" class="block w-full cursor-pointer {{ $hasCur ? 'hidden' : '' }}">
        <div class="w-full py-10 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition-all">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold">Click to upload {{ $label }}</p>
            <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP supported</p>
        </div>
        <input type="file" id="{{ $uid }}-input" accept="image/*" class="hidden">
    </label>

    {{-- Actual hidden file input submitted with form (no required attr — server-side validates) --}}
    <input type="file" name="{{ $inputName }}" id="{{ $uid }}-final" class="hidden">

    @if(!empty($hint))
    <p class="text-xs text-slate-400 mt-2">{{ $hint }}</p>
    @endif
</div>

{{-- Crop Modal --}}
<div id="{{ $uid }}-modal" class="fixed inset-0 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     style="display:none; z-index:10000 !important;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col" style="max-height:90vh;">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 flex-shrink-0">
            <div>
                <h3 class="text-xl font-bold text-slate-900 font-outfit">Crop {{ $label }}</h3>
                <p class="text-xs text-slate-500 mt-1 italic">
                    @if($ratio !== null)
                        Aspect ratio: {{ $ratio == 1 ? '1:1 (Square)' : round($ratio, 2) . ':1' }}
                    @else
                        Free crop — drag handles to resize
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="ciuRotate('{{ $uid }}')"
                        class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Rotate 90°
                </button>
                <div class="h-8 w-px bg-slate-200 mx-1"></div>
                <button type="button" onclick="ciuClose('{{ $uid }}')"
                        class="px-5 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-semibold hover:bg-rose-50 hover:text-rose-600 transition-all">
                    Cancel
                </button>
                <button type="button" onclick="ciuApply('{{ $uid }}', {{ $outW }}, {{ $outH }})"
                        class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl active:scale-95">
                    Apply & Save
                </button>
            </div>
        </div>
        {{-- Viewport --}}
        <div class="flex-1 min-h-0 bg-[#121216] flex items-center justify-center overflow-hidden p-6 relative" style="min-height:300px;">
            <img id="{{ $uid }}-crop-img" src="" alt="" class="max-h-full max-w-full">
            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-xl px-6 py-2 rounded-full border border-white/10 text-white/80 text-[10px] font-bold uppercase tracking-wider flex items-center gap-3 pointer-events-none">
                <span>Drag to move</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Scroll to zoom</span>
                @if($ratio !== null)
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Fixed ratio</span>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
window._ciu = {};

function ciuInit(uid, aspectRatio) {
    const input = document.getElementById(uid + '-input');
    if (!input) return;
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = document.getElementById(uid + '-crop-img');
            img.src = ev.target.result;
            document.getElementById(uid + '-modal').style.display = 'flex';
            if (window._ciu[uid]) { window._ciu[uid].destroy(); }
            window._ciu[uid] = new Cropper(img, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };
        reader.readAsDataURL(file);
    });
}

function ciuClose(uid) {
    document.getElementById(uid + '-modal').style.display = 'none';
    if (window._ciu[uid]) { window._ciu[uid].destroy(); delete window._ciu[uid]; }
    document.getElementById(uid + '-input').value = '';
}

function ciuRotate(uid) {
    if (window._ciu[uid]) window._ciu[uid].rotate(90);
}

function ciuApply(uid, outW, outH) {
    const cropper = window._ciu[uid];
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({ width: outW, height: outH });
    canvas.toBlob(function(blob) {
        // Always use a safe, space-free filename so Spatie conversions work reliably
        const safeName = 'upload-' + Date.now() + '.jpg';
        const file = new File([blob], safeName, { type: 'image/jpeg' });
        const dt   = new DataTransfer();
        dt.items.add(file);
        document.getElementById(uid + '-final').files = dt.files;
        document.getElementById(uid + '-preview').src = canvas.toDataURL('image/jpeg');
        document.getElementById(uid + '-preview-container').classList.remove('hidden');
        document.getElementById(uid + '-upload-label').classList.add('hidden');
        ciuClose(uid);
    }, 'image/jpeg');
}

function ciuReset(uid) {
    document.getElementById(uid + '-final').value = '';
    document.getElementById(uid + '-input').value  = '';
    document.getElementById(uid + '-preview-container').classList.add('hidden');
    document.getElementById(uid + '-upload-label').classList.remove('hidden');
}
</script>
@endonce
<script>document.addEventListener('DOMContentLoaded', function(){ ciuInit('{{ $uid }}', {{ $jsRatio }}); });</script>
@endpush
