<style>
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
    
    .form-group select {
        max-height: 45px;
    }

    .form-group input:focus,
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

    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #dc2626;
    }

    .image-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .image-upload-area:hover {
        border-color: #0071c2;
        background: #eff6ff;
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .image-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-remove-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(220, 38, 38, 0.9);
        color: white;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        line-height: 1;
        transition: all 0.2s ease;
    }

    .image-remove-btn:hover {
        background: #991b1b;
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
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        width: 100%;
        justify-content: center;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Form Validation Styles */
    .form-error {
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
        display: none;
    }

    .form-error.show {
        display: block;
    }

    .form-group input.error,
    .form-group textarea.error,
    .form-group select.error {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    .form-group input.error:focus,
    .form-group textarea.error:focus,
    .form-group select.error:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }

    /* Image Carousel Styles */
    .image-carousel-container {
        position: relative;
        background: #f9fafb;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .image-carousel-wrapper {
        overflow: hidden;
        width: 100%;
    }

    .image-carousel {
        display: flex;
        transition: transform 0.3s ease;
    }

    .carousel-image-item {
        min-width: 100%;
        position: relative;
        aspect-ratio: 16/9;
    }

    .carousel-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.2s ease;
        line-height: 1;
        padding: 0;
    }

    .carousel-btn:hover {
        background: rgba(0, 0, 0, 0.8);
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-prev {
        left: 16px;
    }

    .carousel-next {
        right: 16px;
    }

    .carousel-indicators {
        display: flex;
        justify-content: center;
        gap: 8px;
        padding: 16px;
        background: #f9fafb;
    }

    .indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #d1d5db;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .indicator.active {
        background: #0071c2;
        transform: scale(1.2);
    }

    .indicator:hover {
        background: #9ca3af;
    }

</style>

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

<form action="{{ isset($property) ? route('admin.properties.update', $property->id) : route('admin.properties.store') }}" method="POST" enctype="multipart/form-data" id="propertyForm" novalidate>
    @csrf
    
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
                   value="{{ old('name', isset($property) ? $property->name : '') }}" 
                   placeholder="Grand Hotel & Resort"
                   required>
            <div class="form-error" id="name-error">This is a required field.</div>
            <div class="form-help">The official name of the property</div>
        </div>

        <div class="form-group">
            <label for="description">
                Description <span class="required">*</span>
            </label>
            <textarea id="description" name="description" 
                      placeholder="Describe your property, its features, and what makes it special..."
                      required>{{ old('description', isset($property) ? $property->description : '') }}</textarea>
            <div class="form-error" id="description-error">This is a required field.</div>
            <div class="form-help">Detailed description that will be shown to guests</div>
        </div>
    </div>

    <!-- Location -->
    <div class="form-section">
        <h2 class="section-title">
            
            Location
        </h2>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="city">
                    City <span class="required">*</span>
                </label>
                <input type="text" id="city" name="city" 
                       value="{{ old('city', isset($property) ? $property->city : '') }}" 
                       placeholder="New York"
                       required
                       oninput="validateLocationField(this)"
                       onblur="validateLocationField(this)">
                <div class="form-error" id="city-error">This field is required</div>
                
            </div>

            <div class="form-group">
                
                <label for="state">
                    State
                </label>
                <input type="text" id="state" name="state" 
                       value="{{ old('state', isset($property) ? $property->state : '') }}" 
                       placeholder="New York"
                       oninput="validateLocationField(this)"
                       onblur="validateLocationField(this)">
                <div class="form-error" id="state-error">Must contain at least one letter</div>
                
            </div>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="country">
                    Country <span class="required">*</span>
                </label>
                <input type="text" id="country" name="country" 
                       value="{{ old('country', isset($property) ? $property->country : '') }}" 
                       placeholder="United States"
                       required
                       oninput="validateLocationField(this)"
                       onblur="validateLocationField(this)">
                <div class="form-error" id="country-error">This field is required</div>
                
            </div>

            <div class="form-group">
                <label for="postal_code">
                    Postal Code
                </label>
                <input type="text" id="postal_code" name="postal_code" 
                       value="{{ old('postal_code', isset($property) ? $property->postal_code : '') }}" 
                       placeholder="10001"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <div class="form-help">Optional - Numbers only</div>
            </div>
        </div>

        <div class="form-group">
            <label for="address">
                Full Address <span class="required">*</span>
            </label>
            <textarea id="address" name="address" 
                      placeholder="123 Main Street, Suite 100..."
                      required>{{ old('address', isset($property) ? $property->address : '') }}</textarea>
            <div class="form-error" id="address-error">This is a required field.</div>
            <div class="form-help">Complete street address</div>
        </div>

        <div class="form-group">
            <label for="location">
                Location Display Text
            </label>
            <input type="text" id="location" name="location" 
                   value="{{ old('location', isset($property) ? $property->location : '') }}" 
                   placeholder="Downtown Manhattan">
            <div class="form-help">Optional: Custom location description for display (e.g., "Downtown Manhattan", "Near Central Park")</div>
        </div>
    </div>

    <!-- Status & Images -->
    <div class="form-section">
        <h2 class="section-title">
           
            Status & Images
        </h2>

        <div class="form-group">
            <label for="status">
                Publishing Status <span class="required">*</span>
            </label>
            <select id="status" name="status" required>
                <option value="draft" {{ old('status', isset($property) ? $property->status : 'draft') === 'draft' ? 'selected' : '' }}>
                    Draft (Hidden from guests)
                </option>
                <option value="published" {{ old('status', isset($property) ? $property->status : '') === 'published' ? 'selected' : '' }}>
                    Published (Visible to guests)
                </option>
            </select>
            <div class="form-help">Draft properties won't be visible to guests</div>
        </div>

        <div class="form-group">
            <label style="display: block; font-weight: 600; color: #262626; margin-bottom: 12px; font-size: 15px;">
                Property Images (Click area below to upload)
            </label>
            <div class="image-upload-area" onclick="document.getElementById('images').click()" style="border: 2px dashed #0071c2; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer; background: #f0f9ff; min-height: 150px;">
                <div style="font-size: 48px; margin-bottom: 12px;">📷</div>
                <div style="font-weight: 600; margin-bottom: 8px; color: #0071c2; font-size: 16px;">Click here to upload images</div>
                <div style="font-size: 14px; color: #6b6b6b;">You can select multiple images at once (JPG, PNG, WebP)</div>
            </div>
            <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(event)">
            <div class="form-help" style="margin-top: 8px; font-size: 13px; color: #6b6b6b;">Upload high-quality images of your property</div>
            <div id="imagePreviewGrid" class="image-preview-grid"></div>
        </div>

        @if(isset($property) && $property->images && $property->images->count() > 0)
        <div class="form-group">
            <label>Existing Images</label>
            <div class="image-carousel-container">
                <button type="button" class="carousel-btn carousel-prev" onclick="prevImage()" style="display: none;">‹</button>
                <div class="image-carousel-wrapper">
                    <div class="image-carousel" id="imageCarousel">
                        @foreach($property->images as $index => $image)
                        <div class="carousel-image-item" data-index="{{ $index }}" data-image-id="{{ $image->id }}">
                            <img src="{{ $image->image_url }}" alt="Property image">
                            <button type="button" class="image-remove-btn" onclick="deleteImage(event, {{ $image->id }})">×</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="carousel-btn carousel-next" onclick="nextImage()">›</button>
            </div>
            <div class="carousel-indicators" id="carouselIndicators">
                @foreach($property->images as $index => $image)
                <span class="indicator {{ $index === 0 ? 'active' : '' }}" onclick="goToImage({{ $index }})"></span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary" id="submitBtn">
       
        <span id="btnText">{{ isset($property) ? 'Update Property' : 'Create Property' }}</span>
        <span id="btnLoading" style="display: none;">{{ isset($property) ? 'Updating...' : 'Creating...' }}</span>
    </button>
</form>

<script>
// Location field validation - ONLY allows letters and spaces (no numbers)
function validateLocationField(field) {
    // Remove any invalid characters (keep ONLY letters and spaces)
    field.value = field.value.replace(/[^A-Za-z\s]/g, '');
    
    // Check if the field contains at least one letter
    const hasLetter = /[a-zA-Z]/.test(field.value);
    const errorElement = document.getElementById(field.id + '-error');
    
    if (field.value.trim() !== '' && !hasLetter) {
        // Field has content but no letters (only spaces)
        field.classList.add('error');
        if (errorElement) {
            errorElement.textContent = 'Must contain at least one letter';
            errorElement.classList.add('show');
        }
    } else if (field.hasAttribute('required') && field.value.trim() === '') {
        // Required field is empty
        field.classList.add('error');
        if (errorElement) {
            errorElement.textContent = 'This field is required';
            errorElement.classList.add('show');
        }
    } else {
        // Field is valid
        field.classList.remove('error');
        if (errorElement) {
            errorElement.classList.remove('show');
        }
    }
}

// Check for duplicate property name
const currentPropertyId = {{ isset($property) ? $property->id : 'null' }};
let propertyNameCheckTimeout;

async function checkDuplicatePropertyName() {
    const nameInput = document.getElementById('name');
    const name = nameInput.value.trim();
    const errorElement = document.getElementById('name-error');
    
    if (name.length < 2) {
        return; // Don't check if too short
    }
    
    // Clear previous timeout
    clearTimeout(propertyNameCheckTimeout);
    
    // Set new timeout for debouncing
    propertyNameCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch('{{ route("admin.properties") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.data && data.data.data) {
                const properties = data.data.data;
                const duplicate = properties.find(p => 
                    p.name.toLowerCase() === name.toLowerCase() && 
                    p.id !== currentPropertyId
                );
                
                if (duplicate) {
                    nameInput.classList.add('error');
                    nameInput.style.borderColor = '#dc3545';
                    if (errorElement) {
                        errorElement.textContent = 'A property with this name already exists';
                        errorElement.classList.add('show');
                    }
                } else {
                    nameInput.classList.remove('error');
                    nameInput.style.borderColor = '';
                    if (errorElement && errorElement.textContent === 'A property with this name already exists') {
                        errorElement.classList.remove('show');
                    }
                }
            }
        } catch (error) {
            console.error('Error checking duplicate name:', error);
        }
    }, 500); // Wait 500ms after user stops typing
}

// Add listener to name input
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', checkDuplicatePropertyName);
        nameInput.addEventListener('blur', checkDuplicatePropertyName);
    }
});

// Inline validation function
function validateField(field) {
    const value = field.value.trim();
    const errorElement = document.getElementById(field.id + '-error');
    
    if (field.hasAttribute('required') && value === '') {
        field.classList.add('error');
        if (errorElement) errorElement.classList.add('show');
        return false;
    } else {
        field.classList.remove('error');
        if (errorElement) errorElement.classList.remove('show');
        return true;
    }
}

// Add validation listeners to all required fields
document.addEventListener('DOMContentLoaded', function() {
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        // Skip file inputs
        if (field.type === 'file') return;
        
        field.addEventListener('blur', function() {
            validateField(this);
        });
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
        field.addEventListener('change', function() {
            validateField(this);
        });
    });
});

let selectedImages = [];

function previewImages(event) {
    const files = Array.from(event.target.files);
    selectedImages = selectedImages.concat(files);
    updateImagePreview();
}

function updateImagePreview() {
    const grid = document.getElementById('imagePreviewGrid');
    grid.innerHTML = '';
    
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="image-remove-btn" onclick="removeImage(${index})">×</button>
            `;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    updateImagePreview();
}

// Prevent multiple form submissions with flag
let isSubmitting = false;

const form = document.getElementById('propertyForm');
const submitBtn = document.getElementById('submitBtn');
const btnText = document.getElementById('btnText');
const btnLoading = document.getElementById('btnLoading');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate all required fields (except file inputs)
    const requiredFields = Array.from(form.querySelectorAll('[required]')).filter(field => field.type !== 'file');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Additional validation for location fields (city, state, country)
    const locationFields = ['city', 'state', 'country'];
    locationFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && field.value.trim() !== '') {
            const hasLetter = /[a-zA-Z]/.test(field.value);
            if (!hasLetter) {
                const errorElement = document.getElementById(fieldId + '-error');
                field.classList.add('error');
                if (errorElement) {
                    errorElement.textContent = 'Must contain at least one letter';
                    errorElement.classList.add('show');
                }
                isValid = false;
            }
        }
    });
    
    if (!isValid) {
        return false;
    }
    
    // If already submitting, ignore this click
    if (isSubmitting) {
        return false;
    }
    
    // Set flag to prevent further submissions
    isSubmitting = true;
    
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.6';
    submitBtn.style.cursor = 'not-allowed';
    submitBtn.style.pointerEvents = 'none';
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    
    // Create FormData and manually append selected images
    const formData = new FormData(form);
    
    // Remove any existing images[] entries from the form
    formData.delete('images[]');
    
    // Add all selected images to FormData
    selectedImages.forEach((file, index) => {
        formData.append('images[]', file);
    });
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        // Success responses
        if (response.ok || response.status === 302) {
            return response.json().then(data => {
                // Store success message in localStorage
                const message = data.message || 'Property saved successfully!';
                localStorage.setItem('toastMessage', message);
                localStorage.setItem('toastType', 'success');
                
                // Close panel and reload page
                closeSlidePanel();
                window.location.reload();
            });
        } 
        // Validation error (422)
        else if (response.status === 422) {
            return response.json().then(data => {
                if (data.errors) {
                    // Clear all previous errors
                    document.querySelectorAll('.form-error.show').forEach(el => {
                        el.classList.remove('show');
                    });
                    document.querySelectorAll('input.error, textarea.error, select.error').forEach(el => {
                        el.classList.remove('error');
                    });
                    
                    // Display inline validation errors
                    Object.keys(data.errors).forEach(key => {
                        const field = document.getElementById(key);
                        const errorElement = document.getElementById(key + '-error');
                        
                        if (field) {
                            field.classList.add('error');
                        }
                        
                        if (errorElement) {
                            errorElement.textContent = data.errors[key][0];
                            errorElement.classList.add('show');
                        }
                    });
                    
                    // Show toast with first error
                    const firstError = Object.values(data.errors)[0][0];
                    showToast(firstError, 'error');
                    
                    // Reset submission state
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    submitBtn.style.pointerEvents = 'auto';
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            });
        }
        // Other errors
        else {
            return response.text().then(text => {
                console.error('Server response:', text);
                console.error('Status code:', response.status);
                throw new Error(`Server error (${response.status}). Check console for details.`);
            });
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        if (error.message && !error.message.includes('Server error')) {
            alert('Error: ' + error.message);
        }
        isSubmitting = false;
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        submitBtn.style.pointerEvents = 'auto';
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    });
});

// Also prevent multiple clicks on the button itself
submitBtn.addEventListener('click', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});

// Image Carousel Functions
let currentCarouselIndex = 0;

function updateCarouselDisplay() {
    const carousel = document.getElementById('imageCarousel');
    const items = carousel.querySelectorAll('.carousel-image-item');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    const indicators = document.querySelectorAll('.indicator');
    
    // Update carousel position
    carousel.style.transform = `translateX(-${currentCarouselIndex * 100}%)`;
    
    // Update indicators
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentCarouselIndex);
    });
    
    // Update button visibility
    if (prevBtn) prevBtn.style.display = currentCarouselIndex === 0 ? 'none' : 'flex';
    if (nextBtn) nextBtn.style.display = currentCarouselIndex === items.length - 1 ? 'none' : 'flex';
}

function nextImage() {
    const carousel = document.getElementById('imageCarousel');
    const items = carousel.querySelectorAll('.carousel-image-item');
    
    if (currentCarouselIndex < items.length - 1) {
        currentCarouselIndex++;
        updateCarouselDisplay();
    }
}

function prevImage() {
    if (currentCarouselIndex > 0) {
        currentCarouselIndex--;
        updateCarouselDisplay();
    }
}

function goToImage(index) {
    currentCarouselIndex = index;
    updateCarouselDisplay();
}

function deleteImage(event, imageId) {
    event.preventDefault();
    event.stopPropagation();
    
    showConfirmDialog(
        'Are you sure?',
        'Do you really want to delete this image? This process cannot be undone.',
        function() {
            // Send delete request
            fetch(`{{ url('admin/properties/images') }}/${imageId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Remove the image from carousel
                const carousel = document.getElementById('imageCarousel');
                const item = carousel.querySelector(`[data-image-id="${imageId}"]`);
                const indicators = document.getElementById('carouselIndicators');
                
                if (item) {
                    const index = parseInt(item.getAttribute('data-index'));
                    item.remove();
                    
                    // Remove corresponding indicator
                    const indicatorElements = indicators.querySelectorAll('.indicator');
                    if (indicatorElements[index]) {
                        indicatorElements[index].remove();
                    }
                    
                    // Reindex remaining items
                    const remainingItems = carousel.querySelectorAll('.carousel-image-item');
                    remainingItems.forEach((el, idx) => {
                        el.setAttribute('data-index', idx);
                    });
                    
                    // Adjust current index if needed
                    if (currentCarouselIndex >= remainingItems.length) {
                        currentCarouselIndex = Math.max(0, remainingItems.length - 1);
                    }
                    
                    // Update display or hide carousel if no images left
                    if (remainingItems.length === 0) {
                        document.querySelector('.image-carousel-container').parentElement.style.display = 'none';
                    } else {
                        updateCarouselDisplay();
                    }
                    
                    showToast('Image deleted successfully', 'success');
                }
            })
            .catch(error => {
                showToast('Error deleting image', 'error');
                console.error('Error:', error);
            });
        }
    );
}

// Initialize carousel on page load
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('imageCarousel');
    if (carousel && carousel.querySelectorAll('.carousel-image-item').length > 0) {
        updateCarouselDisplay();
    }
});

</script>
