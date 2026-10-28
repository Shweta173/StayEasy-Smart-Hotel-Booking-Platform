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
    .form-group input[type="number"],
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

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
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

<form action="{{ isset($roomType) ? route('admin.room-types.update', $roomType->id) : route('admin.room-types.store') }}" method="POST" id="roomTypeForm" novalidate>
    @csrf
    
    <!-- Property Selection -->
    <div class="form-section">
        <h2 class="section-title">
          
            Property
        </h2>

        <div class="form-group">
            <label for="property_id">
                Select Property <span class="required">*</span>
            </label>
            <select id="property_id" name="property_id" required>
                <option value="">Choose a property...</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ old('property_id', isset($roomType) ? $roomType->property_id : '') == $property->id ? 'selected' : '' }}>
                        {{ $property->name }}@if($property->location) ({{ $property->location }})@endif
                        @if($property->status === 'draft')
                            - [DRAFT]
                        @endif
                    </option>
                @endforeach
            </select>
            <div class="form-error" id="property_id-error">This is a required field.</div>
            <div class="form-help">Select which property this room type belongs to</div>
        </div>
    </div>

    <!-- Room Details -->
    <div class="form-section">
        <h2 class="section-title">
            
            Room Details
        </h2>

        <div class="form-group">
            <label for="name">
                Room Type Name <span class="required">*</span>
            </label>
            <input type="text" id="name" name="name" 
                   value="{{ old('name', isset($roomType) ? $roomType->name : '') }}" 
                   placeholder="e.g., Standard Double Room"
                   required>
            <div class="form-error" id="name-error">This field is required</div>
            <div class="form-help">Name that guests will see (e.g., Deluxe Suite, Standard Room)</div>
        </div>

        <div class="form-group">
            <label for="description">
                Description <span class="required">*</span>
            </label>
            <textarea id="description" name="description" 
                      placeholder="Describe the room features, bed types, size, and amenities..."
                      required>{{ old('description', isset($roomType) ? $roomType->description : '') }}</textarea>
            <div class="form-error" id="description-error">This field is required</div>
            <div class="form-help">Provide a detailed description that will be shown to guests</div>
        </div>
    </div>

    <!-- Pricing & Capacity -->
    <div class="form-section">
        <h2 class="section-title">
            
            Pricing & Capacity
        </h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="base_price">
                    Base Price per Night <span class="required">*</span>
                </label>
                <input type="number" id="base_price" name="base_price" 
                       value="{{ old('base_price', isset($roomType) ? $roomType->base_price : '') }}" 
                       step="0.01" 
                       min="0.01"
                       placeholder="199.00"
                       required
                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                <div class="form-error" id="base_price-error">This field is required</div>
                <div class="form-help">Standard nightly rate (can be overridden by rate plans)</div>
            </div>

            <div class="form-group">
                <label for="max_occupancy">
                    Maximum Occupancy <span class="required">*</span>
                </label>
                <input type="number" id="max_occupancy" name="max_occupancy" 
                       value="{{ old('max_occupancy', isset($roomType) ? $roomType->max_occupancy : '') }}" 
                       min="1"
                       placeholder="2"
                       required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <div class="form-error" id="max_occupancy-error">This field is required</div>
                <div class="form-help">Maximum number of guests allowed</div>
            </div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="form-section">
        <h2 class="section-title">
            
            Inventory
        </h2>

        <div class="form-group">
            <label for="total_units">
                Number of Units <span class="required">*</span>
            </label>
            <input type="number" id="total_units" name="total_units" 
                   value="{{ old('total_units', isset($roomType) ? $roomType->total_units : '') }}" 
                   min="1"
                   placeholder="10"
                   required
                   oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            <div class="form-error" id="total_units-error">This field is required</div>
            <div class="form-help">Total number of rooms of this type available at the property</div>
        </div>
    </div>

    <!-- Amenities -->
    <div class="form-section">
        <h2 class="section-title">
            ✨
            Amenities
        </h2>

        @php
            $allAmenities = App\Models\Amenity::orderBy('name')->get();
            $selectedAmenityIds = old('amenities', isset($roomType) ? $roomType->amenities->pluck('id')->toArray() : []);
        @endphp

        @if($allAmenities->count() > 0)
        <div class="form-group">
            <label>Select Amenities</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 12px;">
                @foreach($allAmenities as $amenity)
                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;">
                    <input 
                        type="checkbox" 
                        name="amenities[]" 
                        value="{{ $amenity->id }}"
                        {{ in_array($amenity->id, $selectedAmenityIds) ? 'checked' : '' }}
                        style="width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span style="flex: 1; font-size: 14px; color: #262626; font-weight: 500;">
                        {{ $amenity->name }}
                    </span>
                </label>
                @endforeach
            </div>
            <div class="form-help">Check all amenities available for this room type</div>
        </div>
        @else
        <div style="padding: 24px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; text-align: center;">
            <p style="color: #92400e; font-weight: 600; margin-bottom: 8px;">No amenities available</p>
            <p style="color: #92400e; font-size: 14px;">Please create amenities first in the Amenities page, then you can assign them to room types.</p>
            <a href="{{ route('admin.amenities') }}" style="display: inline-block; margin-top: 12px; padding: 8px 16px; background: #f59e0b; color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
                Go to Amenities →
            </a>
        </div>
        @endif
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary" id="submitBtn">
        
        <span id="btnText">{{ isset($roomType) ? 'Update Room Type' : 'Create Room Type' }}</span>
        <span id="btnLoading" style="display: none;">{{ isset($roomType) ? 'Updating...' : 'Creating...' }}</span>
    </button>
</form>

<script>
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

// Prevent multiple form submissions with flag
let isSubmitting = false;

const form = document.getElementById('roomTypeForm');
const submitBtn = document.getElementById('submitBtn');
const btnText = document.getElementById('btnText');
const btnLoading = document.getElementById('btnLoading');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate all required fields
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
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
    
    // Disable button immediately
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.6';
    submitBtn.style.cursor = 'not-allowed';
    submitBtn.style.pointerEvents = 'none';
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    
    // Submit the form using FormData
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Success responses
        if (response.ok) {
            // Store success message in localStorage
            const isEdit = form.action.includes('/edit') || document.querySelector('input[name="_method"]');
            const message = isEdit ? 'Room Type updated successfully!' : 'Room Type created successfully!';
            localStorage.setItem('toastMessage', message);
            localStorage.setItem('toastType', 'success');
            
            // Close panel and reload page
            closeSlidePanel();
            window.location.reload();
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
                throw new Error('Form submission failed');
            });
        }
    })
    .catch(error => {
        if (error.message && error.message !== 'Form submission failed') {
            alert('Error: ' + error.message);
        }
        // Reset flag and button on error
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
</script>
