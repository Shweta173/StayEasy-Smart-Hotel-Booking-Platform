@extends('admin.layout')

@section('title', 'Edit Property')

@section('content')
<style>
    .form-header {
        margin-bottom: 32px;
    }

    .form-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 8px;
    }

    .form-header p {
        color: #6b6b6b;
        font-size: 15px;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        max-width: 900px;
    }

    .form-section {
        margin-bottom: 32px;
        padding-bottom: 32px;
        border-bottom: 1px solid #e0e0e0;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #262626;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group label .required {
        color: #dc2626;
    }

    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-group input[type="text"]:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-help {
        font-size: 13px;
        color: #6b6b6b;
        margin-top: 6px;
    }

    .existing-images {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .existing-image {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
    }

    .existing-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0, 113, 194, 0.9);
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .image-remove {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 38, 38, 0.9);
        color: white;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .image-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .file-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        background: #f9fafb;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: #0071c2;
        background: #f0f9ff;
    }

    .upload-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .upload-text {
        font-size: 15px;
        color: #262626;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .upload-hint {
        font-size: 13px;
        color: #6b6b6b;
    }

    .file-upload-area input[type="file"] {
        display: none;
    }

    .image-preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }

    .preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-remove {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 38, 38, 0.9);
        color: white;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .preview-remove:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #e0e0e0;
        margin-top: 32px;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: #0071c2;
        color: white;
    }

    .btn-primary:hover {
        background: #00509e;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #262626;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .status-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .status-option {
        position: relative;
    }

    .status-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .status-option label {
        display: block;
        padding: 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        margin: 0;
    }

    .status-option input[type="radio"]:checked + label {
        border-color: #0071c2;
        background: #e0f2fe;
    }

    .status-option label .status-icon {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .status-option label .status-title {
        font-weight: 700;
        color: #262626;
        margin-bottom: 4px;
    }

    .status-option label .status-desc {
        font-size: 12px;
        color: #6b6b6b;
        font-weight: 400;
    }

    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #dc2626;
    }
</style>

<div class="form-header">
    <h1>✏️ Edit Property</h1>
    <p>Update property information and settings</p>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <span>⚠️</span>
    <div>
        <strong>Please fix the following errors:</strong>
        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form action="{{ route('admin.properties.update', $property->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="form-card">
        <!-- Basic Information -->
        <div class="form-section">
            <h2 class="section-title">
               
                Basic Information
            </h2>
            
            <div class="form-group">
                <label for="name">
                    Property Name <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name" 
                       value="{{ old('name', $property->name) }}" 
                       placeholder="e.g., Grand Oceanview Resort"
                       required>
                <div class="form-help">Enter the full name of the property</div>
            </div>

            <div class="form-group">
                <label for="description">
                    Description <span class="required">*</span>
                </label>
                <textarea id="description" name="description" 
                          placeholder="Describe the property, its features, and what makes it special..."
                          required>{{ old('description', $property->description) }}</textarea>
                <div class="form-help">Provide a detailed description that will be shown to guests</div>
            </div>
        </div>

        <!-- Location Details -->
        <div class="form-section">
            <h2 class="section-title">
               
                Location Details
            </h2>

            <div class="form-group">
                <label for="location">
                    Location <span class="required">*</span>
                </label>
                <input type="text" id="location" name="location" 
                       value="{{ old('location', $property->location) }}" 
                       placeholder="e.g., Miami Beach, Florida"
                       required>
                <div class="form-help">City and state/region</div>
            </div>

            <div class="form-group">
                <label for="address">
                    Full Address <span class="required">*</span>
                </label>
                <textarea id="address" name="address" 
                          placeholder="Enter complete street address with postal code"
                          rows="3"
                          required>{{ old('address', $property->address) }}</textarea>
                <div class="form-help">Complete address including street, city, state, and postal code</div>
            </div>
        </div>

        <!-- Property Images -->
        <div class="form-section">
            <h2 class="section-title">
                <span>📸</span>
                Property Images
                <span id="imgCountBadge" style="margin-left:8px;background:#0071c2;color:white;font-size:12px;font-weight:700;padding:2px 10px;border-radius:20px;display:none;">0 new</span>
            </h2>

            {{-- Existing saved images --}}
            @if($property->images->count() > 0)
            <p style="font-size:12px;color:#6b7280;margin-bottom:10px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
                Saved images ({{ $property->images->count() }})
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px;margin-bottom:22px;">
                @foreach($property->images as $image)
                <div style="position:relative;border-radius:8px;overflow:hidden;border:2px solid {{ $image->is_primary ? '#0071c2' : '#e0e0e0' }};aspect-ratio:1;background:#f3f4f6;">
                    <img src="{{ asset($image->image_url) }}" alt="Property image"
                         style="width:100%;height:100%;object-fit:cover;display:block;"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300'; console.error('Failed to load image: {{ $image->image_url }}');"

                    @if($image->is_primary)
                        <div style="position:absolute;top:6px;left:6px;background:#0071c2;color:white;font-size:9px;font-weight:700;padding:2px 8px;border-radius:10px;text-transform:uppercase;">Primary</div>
                    @endif

                    {{-- Delete button --}}
                    <form action="{{ route('admin.properties.delete-image', $image->id) }}" method="POST"
                          onsubmit="return confirm('Delete this image?')" style="margin:0;">
                        @csrf
                        <button type="submit" title="Delete"
                            style="position:absolute;top:6px;right:6px;background:rgba(220,38,38,.9);color:white;border:none;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:14px;font-weight:700;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
                    </form>

                    {{-- Set as primary --}}
                    @if(!$image->is_primary)
                    <button type="button"
                        onclick="setPrimary({{ $image->id }}, this)"
                        style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);color:white;border:none;padding:4px 0;font-size:10px;font-weight:600;cursor:pointer;text-transform:uppercase;letter-spacing:.3px;">
                        Set Primary
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Drop zone for new images --}}
            <div id="dropZone" style="border:2px dashed #d1d5db;border-radius:12px;padding:28px;text-align:center;background:#f9fafb;cursor:pointer;transition:all .2s;"
                 onclick="document.getElementById('images').click()">
                <div style="font-size:40px;margin-bottom:10px;">🖼️</div>
                <div style="font-size:15px;font-weight:600;color:#262626;margin-bottom:6px;">Click or drag &amp; drop to add more images</div>
                <div style="font-size:13px;color:#6b7280;">JPG, PNG, WebP — up to 10 MB each</div>
                <input type="file" id="images" name="images[]" multiple accept="image/*" style="display:none;">
            </div>

            {{-- New-image preview grid --}}
            <div id="imagePreview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px;margin-top:16px;"></div>
        </div>

        <!-- Publishing Status -->
        <div class="form-section">
            <h2 class="section-title">
                <span>🚀</span>
                Publishing Status
            </h2>

            <div class="status-selector">
                <div class="status-option">
                    <input type="radio" id="status_draft" name="status" value="draft" 
                           {{ old('status', $property->status) === 'draft' ? 'checked' : '' }}>
                    <label for="status_draft">
                       
                        <div class="status-title">Draft</div>
                        <div class="status-desc">Save as draft, not visible to guests</div>
                    </label>
                </div>

                <div class="status-option">
                    <input type="radio" id="status_published" name="status" value="published"
                           {{ old('status', $property->status) === 'published' ? 'checked' : '' }}>
                    <label for="status_published">
                        <div class="status-icon">✅</div>
                        <div class="status-title">Published</div>
                        <div class="status-desc">Make visible to guests immediately</div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.properties') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span>💾</span>
                <span id="btnText">Update Property</span>
                <span id="btnLoading" style="display: none;">Updating...</span>
            </button>
        </div>
    </div>
</form>

<script>
var selectedFiles = [];

document.querySelector('form').addEventListener('submit', function(e) {
    if (selectedFiles.length === 0) return; // no new images — submit normally

    e.preventDefault();

    var submitBtn = document.getElementById('submitBtn');
    var btnText   = document.getElementById('btnText');
    var btnLoading= document.getElementById('btnLoading');

    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.6';
    btnText.style.display    = 'none';
    btnLoading.style.display = 'inline';
    btnLoading.textContent   = 'Uploading ' + selectedFiles.length + ' image(s)...';

    var form = this;

    // Remove empty file input
    var existingInput = document.getElementById('images');
    if (existingInput) existingInput.parentNode.removeChild(existingInput);

    // One hidden <input type="file"> per file — all sent together natively
    selectedFiles.forEach(function(file) {
        var input = document.createElement('input');
        input.type  = 'file';
        input.name  = 'images[]';
        input.style.display = 'none';
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        form.appendChild(input);
    });

    form.submit();
});

function updateBadge() {
    var badge = document.getElementById('imgCountBadge');
    if (selectedFiles.length > 0) {
        badge.textContent   = selectedFiles.length + (selectedFiles.length === 1 ? ' new image' : ' new images');
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

function renderPreviews() {
    var grid = document.getElementById('imagePreview');
    grid.innerHTML = '';

    selectedFiles.forEach(function(file, idx) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            var item = document.createElement('div');
            item.setAttribute('data-idx', idx);
            item.style.cssText = 'position:relative;border-radius:8px;overflow:hidden;border:2px solid #e0e0e0;aspect-ratio:1;background:#f3f4f6';
            item.innerHTML =
                '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;display:block" alt="">' +
                '<div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);color:#fff;font-size:10px;padding:4px 6px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">' + file.name + '</div>' +
                '<button type="button" data-remove="' + idx + '" style="position:absolute;top:6px;right:6px;background:rgba(220,38,38,.9);color:#fff;border:none;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:16px;font-weight:700;line-height:1">×</button>';
            grid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });

    updateBadge();
}

document.getElementById('imagePreview').addEventListener('click', function(e) {
    var btn = e.target.closest('[data-remove]');
    if (!btn) return;
    selectedFiles.splice(parseInt(btn.getAttribute('data-remove'), 10), 1);
    renderPreviews();
});

document.getElementById('images').addEventListener('change', function() {
    Array.from(this.files).forEach(function(f) { selectedFiles.push(f); });
    this.value = '';
    renderPreviews();
});

var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover',  function(e) { e.preventDefault(); this.style.borderColor='#0071c2'; this.style.background='#e0f2fe'; });
dropZone.addEventListener('dragleave', function()  { this.style.borderColor=''; this.style.background=''; });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '';
    this.style.background  = '';
    Array.from(e.dataTransfer.files)
        .filter(function(f) { return f.type.startsWith('image/'); })
        .forEach(function(f) { selectedFiles.push(f); });
    renderPreviews();
});

function setPrimary(imageId, btn) {
    btn.textContent = '...';
    btn.disabled = true;
    fetch('/admin/properties/images/' + imageId + '/primary', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { window.location.reload(); }
        else { btn.textContent = 'Set Primary'; btn.disabled = false; }
    })
    .catch(function() { btn.textContent = 'Set Primary'; btn.disabled = false; });
}
</script>
@endsection
