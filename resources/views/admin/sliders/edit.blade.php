@extends('layouts.admin')

@section('title', 'Edit Hero Slide')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    #crop-image-element { max-width: 100%; display: block; }
    .cropper-container { width: 100% !important; }
    .cropper-view-box, .cropper-face { border-radius: 4px; }
</style>
@endpush

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold font-outfit text-slate-900">Edit Hero Slide</h2>
        <p class="text-slate-500 mt-1">Update the homepage banner slide content and image.</p>
    </div>
    <a href="{{ route('admin.sliders.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all">← Back</a>
</div>

@if($errors->any())
<div class="glass border-l-4 border-rose-500 p-4 mb-6 rounded-2xl">
    <ul class="list-disc list-inside text-sm text-rose-700 space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">

    <form id="slider-form" action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Slide Image -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Banner Image</label>
            
            <div id="image-preview-container" class="mb-4 relative">
                <p class="text-xs font-medium text-slate-400 mb-1" id="preview-label">Current Image</p>
                <img id="image-preview" src="{{ $slider->image }}" class="h-48 w-full object-cover rounded-xl border border-slate-200 shadow-sm transition-all">
                <button type="button" id="remove-image" class="hidden absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1.5 shadow-lg hover:bg-rose-600 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <input type="file" id="image-input" accept="image/*"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border border-slate-200 rounded-xl p-2 focus:ring-2 focus:ring-indigo-400 outline-none">
            
            <input type="file" name="image" id="final-image" class="hidden">
            <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Recommended: 1920×900px (approx 2.13:1). Max 4MB.
            </p>
        </div>

        <!-- Badge Text -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Badge Text <span class="text-slate-400 font-normal">(e.g. "New Collection", "50% Off")</span></label>
            <input type="text" name="badge_text" value="{{ old('badge_text', $slider->badge_text) }}" placeholder="New Collection"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 shadow-sm transition-all focus:border-indigo-400">
        </div>

        <!-- Title -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Title <span class="text-rose-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $slider->title) }}" required
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 shadow-sm transition-all focus:border-indigo-400">
        </div>

        <!-- Subtitle -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Subtitle</label>
            <input type="text" name="description" value="{{ old('description', $slider->description) }}" placeholder="Premium modern homes & furniture"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 shadow-sm transition-all focus:border-indigo-400">
        </div>

        <!-- Button Text -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Button Label</label>
            <input type="text" name="button_text" value="{{ old('button_text', $slider->button_text ?? 'Shop Now') }}"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 shadow-sm transition-all focus:border-indigo-400">
        </div>

        <!-- Link -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Button Link</label>
            <input type="text" name="link" value="{{ old('link', $slider->link) }}" placeholder="/shop"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 shadow-sm transition-all focus:border-indigo-400">
        </div>

        <!-- Status -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-900 bg-white shadow-sm transition-all focus:border-indigo-400">
                <option value="5" {{ $slider->status == 5 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $slider->status == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-lg active:scale-95">
                Save Changes
            </button>
            <a href="{{ route('admin.sliders.index') }}" class="px-6 py-3 text-slate-600 bg-slate-100 rounded-xl font-semibold hover:bg-slate-200 transition-all">Cancel</a>
        </div>
    </form>
</div>

@push('modals')
<!-- Cropping Modal (FULL SCREEN) -->
<div id="crop-modal" class="fixed inset-0" style="display: none; z-index: 10000 !important;">
    <!-- Dark Backdrop -->
    <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-md transition-opacity" aria-hidden="true" id="close-modal-bg"></div>
    
    <!-- Full Screen Workspace -->
    <div class="relative bg-white w-full h-full flex flex-col shadow-3xl overflow-hidden animate-in">
        <!-- Panel Header -->
        <div class="flex items-center justify-between px-8 py-5 border-b border-slate-100 bg-white z-10 sticky top-0 shadow-sm">
            <div>
                <h3 class="text-xl font-bold text-slate-900 font-outfit">Crop Hero Banner Image</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium italic">Aspect Ratio: 1920x900px</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="button" id="crop-rotate" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Rotate 90°
                </button>
                <div class="h-8 w-px bg-slate-200 mx-1"></div>
                <button type="button" id="crop-cancel" class="px-5 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-semibold hover:bg-rose-50 hover:text-rose-600 transition-all">
                    Cancel
                </button>
                <button type="button" id="crop-save" class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl active:scale-95 ring-offset-2 focus:ring-2 focus:ring-indigo-500">
                    Apply & Save
                </button>
            </div>
        </div>

        <!-- Cropping Viewport -->
        <div class="flex-1 bg-[#121216] flex items-center justify-center overflow-hidden p-8 relative">
            <div class="w-full h-full flex items-center justify-center">
                <img id="crop-image-element" src="" class="max-h-full max-w-full drop-shadow-2xl">
            </div>
            
            <!-- Floating Tips -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-xl px-7 py-3 rounded-full border border-white/10 text-white/90 text-[11px] font-bold uppercase tracking-wider flex items-center gap-4 pointer-events-none shadow-3xl">
                <span>Drag to move</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Scroll to zoom</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Fixed Aspect: 1920x900</span>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image-input');
        const finalImage = document.getElementById('final-image');
        const modal = document.getElementById('crop-modal');
        const cropImageElement = document.getElementById('crop-image-element');
        const cropSave = document.getElementById('crop-save');
        const cropCancel = document.getElementById('crop-cancel');
        const cropRotate = document.getElementById('crop-rotate');
        const closeModalBg = document.getElementById('close-modal-bg');
        const imagePreview = document.getElementById('image-preview');
        const previewLabel = document.getElementById('preview-label');
        const removeImageBtn = document.getElementById('remove-image');
        
        let cropper = null;
        let originalPreviewSrc = imagePreview.src;

        imageInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    cropImageElement.src = event.target.result;
                    modal.style.display = 'flex';
                    
                    if (cropper) cropper.destroy();
                    
                    cropper = new Cropper(cropImageElement, {
                        ratio: 1920 / 900,
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
                reader.readAsDataURL(files[0]);
            }
        });

        const closeModal = () => {
            modal.style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (!finalImage || !finalImage.files.length) {
                imageInput.value = '';
            }
        };

        cropCancel.addEventListener('click', closeModal);
        closeModalBg.addEventListener('click', closeModal);

        cropRotate.addEventListener('click', () => {
            if (cropper) cropper.rotate(90);
        });

        cropSave.addEventListener('click', function() {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({
                width: 1920,
                height: 900,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function(blob) {
                const fileName = imageInput.files[0].name.split('.')[0] + '.jpg';
                const croppedFile = new File([blob], fileName, { type: 'image/jpeg' });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                finalImage.files = dataTransfer.files;

                imagePreview.src = canvas.toDataURL('image/jpeg');
                previewLabel.textContent = 'New Cropped Image';
                removeImageBtn.classList.remove('hidden');
                imageInput.classList.add('hidden');

                closeModal();
            }, 'image/jpeg', 0.92);
        });

        removeImageBtn.addEventListener('click', function() {
            imageInput.value = '';
            const dataTransfer = new DataTransfer();
            finalImage.files = dataTransfer.files;
            imagePreview.src = originalPreviewSrc;
            previewLabel.textContent = 'Current Image';
            removeImageBtn.classList.add('hidden');
            imageInput.classList.remove('hidden');
        });
    });
</script>
@endpush
