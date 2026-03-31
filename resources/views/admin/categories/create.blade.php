@extends('layouts.admin')

@section('title', 'Add Category')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    #crop-image-element { max-width: 100%; display: block; }
    .crop-area-wrapper {
        min-height: 400px;
        max-height: 70vh;
        width: 100%;
        overflow: hidden;
        background-color: #f8fafc;
        border-radius: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="pb-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Add Category</h1>
            <p class="text-sm text-slate-500 mt-1">Standardized admin form layout with card sections.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">Back</a>
            <button type="button" onclick="document.getElementById('category-form').submit()" class="admin-btn-primary">Save Category</button>
        </div>
    </div>

    @include('admin._alerts')

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" id="category-form" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Category Information</h2>
                        <p class="admin-card-subtitle">Use clear names for easy filtering.</p>
                    </div>
                    <div class="admin-form-grid">
                        <div class="admin-form-field">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Living Room Furniture">
                            @error('name') <p class="text-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="admin-form-field">
                            <label for="parent_id">Parent Category</label>
                            <select name="parent_id" id="parent_id">
                                <option value="">Root Category (Master)</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-form-field xl:col-span-2">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" placeholder="Describe this category...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Category Image</h2>
                    </div>
                    <div>
                        <div id="image-preview-container" class="hidden mb-4">
                            <img id="image-preview" src="" class="w-40 h-40 object-cover rounded-xl border border-slate-200">
                            <button type="button" onclick="resetImage()" class="mt-2 text-xs font-bold text-rose-600 hover:text-rose-700 uppercase">Remove & Reset</button>
                        </div>
                        
                        <label id="upload-label" class="block w-full cursor-pointer">
                            <div class="w-full py-10 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition-all">
                                <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                </div>
                                <p class="text-sm font-semibold">Upload image</p>
                                <p class="text-xs text-slate-400">JPG, PNG, WebP (Max 2MB)</p>
                            </div>
                            <input type="file" id="image-input" accept="image/*" class="hidden">
                        </label>
                        <!-- Hidden input for the actual file that will be submitted -->
                        <input type="file" name="image" id="final-image" class="hidden">
                        @error('image') <p class="text-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4 space-y-6">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Visibility</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Active Status</span>
                            <input type="hidden" name="status" value="10">
                            <input type="checkbox" name="status" value="5" checked class="h-4 w-4 text-indigo-600 border-slate-300 rounded">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">Discard</a>
            <button type="submit" class="admin-btn-primary">Save Category</button>
        </div>
    </form>
</div>

<!-- Image Cropping Modal (FULL SCREEN) -->
<div id="crop-modal" class="fixed inset-0 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none; z-index: 10000 !important;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col" style="max-height:90vh;">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Crop Category Image</h3>
                <p class="text-[10px] text-slate-500 font-medium">Ensures professional quality (1:1 square recommended)</p>
                <h3 class="text-xl font-bold text-slate-900 font-outfit">Crop Category Image</h3>
                <p class="text-xs text-slate-500 mt-1 font-medium italic">Aspect Ratio: 1:1 (Square)</p>
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
                <button type="button" id="apply-crop-btn" class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl font-bold hover:from-indigo-500 hover:to-violet-500 transition-all shadow-xl active:scale-95 ring-offset-2 focus:ring-2 focus:ring-indigo-500">
                    Apply & Save
                </button>
            </div>
        </div>

        <!-- Cropping Viewport -->
        <div class="flex-1 min-h-0 bg-[#121216] flex items-center justify-center overflow-hidden p-8 relative">
            <div class="w-full h-full flex items-center justify-center">
                <img id="crop-image-element" src="" class="max-h-full max-w-full drop-shadow-2xl">
            </div>
            
            <!-- Floating Tips -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-xl px-7 py-3 rounded-full border border-white/10 text-white/90 text-[11px] font-bold uppercase tracking-wider flex items-center gap-4 pointer-events-none shadow-3xl">
                <span>Drag to move</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Scroll to zoom</span>
                <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                <span>Squared for categories</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    let cropper = null;
    const imageInput = document.getElementById('image-input');
    const finalImage = document.getElementById('final-image');
    const cropModal = document.getElementById('crop-modal');
    const cropImageElement = document.getElementById('crop-image-element');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const uploadLabel = document.getElementById('upload-label');
    const applyCropBtn = document.getElementById('apply-crop-btn');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                cropImageElement.src = event.target.result;
                openCropModal();
            };
            reader.readAsDataURL(file);
        }
    });

    function openCropModal() {
        cropModal.style.display = 'flex';
        
        if (cropper) {
            cropper.destroy();
        }
        
        cropper = new Cropper(cropImageElement, {
            aspectRatio: 1,
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
    }

    function closeCropModal() {
        cropModal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        imageInput.value = '';
    }

    applyCropBtn.addEventListener('click', function() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 800,
        });

        canvas.toBlob(function(blob) {
            // Create a File object from the blob
            const fileName = imageInput.files[0].name;
            const croppedFile = new File([blob], fileName, { type: 'image/jpeg' });

            // Use DataTransfer to put the file into the hidden input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            finalImage.files = dataTransfer.files;

            // Show preview
            imagePreview.src = canvas.toDataURL('image/jpeg');
            imagePreviewContainer.classList.remove('hidden');
            uploadLabel.classList.add('hidden');

            closeCropModal();
        }, 'image/jpeg');
    });

    function resetImage() {
        finalImage.value = '';
        imageInput.value = '';
        imagePreviewContainer.classList.add('hidden');
        uploadLabel.classList.remove('hidden');
    }
</script>
@endpush
