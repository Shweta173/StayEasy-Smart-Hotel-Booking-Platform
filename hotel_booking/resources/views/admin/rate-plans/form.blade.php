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
    .form-group input[type="date"],
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
        /* Limit dropdown height when opened */
        overflow-y: auto;
    }
    
    /* Limit option text to prevent overflow */
    .form-group select option {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 8px 12px;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
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

    .info-box {
        background: #e0f2fe;
        border-left: 4px solid #0071c2;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .info-box-title {
        font-weight: 700;
        color: #0369a1;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .info-box-text {
        font-size: 13px;
        color: #075985;
        line-height: 1.6;
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

<form action="{{ isset($ratePlan) ? route('admin.rate-plans.update', $ratePlan->id) : route('admin.rate-plans.store') }}" method="POST" id="ratePlanForm" novalidate>
    @csrf
    
    <!-- Room Selection -->
    <div class="form-section">
        <h2 class="section-title">
          
            Room Type Selection
        </h2>

        <div class="info-box">
            <div class="info-box-title">
                <span>💡</span>
                How Rate Plans Work
            </div>
            <div class="info-box-text">
                Rate plans override the base price for specific date ranges. Perfect for seasonal pricing (summer, holidays), special events, or promotional periods.
            </div>
        </div>

        <div class="form-group">
            <label for="room_type_id">
                Select Room Type <span class="required">*</span>
            </label>
            <select id="room_type_id" name="room_type_id" required size="1">
                <option value="">Choose a room type...</option>
                @foreach($allRoomTypes as $roomType)
                    <option value="{{ $roomType->id }}" {{ old('room_type_id', isset($ratePlan) ? $ratePlan->room_type_id : '') == $roomType->id ? 'selected' : '' }}>
                        {{ $roomType->name }} - {{ $roomType->property->name }} - ${{ number_format($roomType->base_price, 2) }}
                        @if($roomType->property->status === 'draft')
                            [DRAFT]
                        @endif
                    </option>
                @endforeach
            </select>
            <div class="form-error" id="room_type_id-error">This is a required field.</div>
            <div class="form-help">All room types from all properties shown</div>
        </div>
    </div>

    <!-- Date Range -->
    <div class="form-section">
        <h2 class="section-title">
           
            Date Range
        </h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="start_date">
                    Start Date <span class="required">*</span>
                </label>
                <input type="date" id="start_date" name="start_date" 
                       value="{{ old('start_date', isset($ratePlan) ? $ratePlan->start_date->format('Y-m-d') : '') }}" 
                       min="{{ date('Y-m-d') }}"
                       required>
                <div class="form-error" id="start_date-error">This field is required</div>
                <div class="form-help">First day of special pricing</div>
            </div>

            <div class="form-group">
                <label for="end_date">
                    End Date <span class="required">*</span>
                </label>
                <input type="date" id="end_date" name="end_date" 
                       value="{{ old('end_date', isset($ratePlan) ? $ratePlan->end_date->format('Y-m-d') : '') }}" 
                       min="{{ date('Y-m-d') }}"
                       required>
                <div class="form-error" id="end_date-error">This field is required</div>
                <div class="form-help">Last day of special pricing</div>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="form-section">
        <h2 class="section-title">
          
            Pricing
        </h2>

        <div class="form-group">
            <label for="price_per_night">
                Price Per Night <span class="required">*</span>
            </label>
            <input type="number" id="price_per_night" name="price_per_night" 
                   value="{{ old('price_per_night', isset($ratePlan) ? $ratePlan->price_per_night : '') }}" 
                   step="0.01" 
                   min="0.01"
                   placeholder="199.00"
                   required
                   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
            <div class="form-error" id="price_per_night-error">This field is required</div>
            <div class="form-help">Set the nightly rate for this date range</div>
        </div>
    </div>

    <!-- Restrictions -->
    <div class="form-section">
        <h2 class="section-title">
            
            Booking Restrictions
        </h2>

        <div class="form-group">
            <label for="min_nights">
                Minimum Nights (Optional)
            </label>
            <input type="number" id="min_nights" name="min_nights" 
                   value="{{ old('min_nights', isset($ratePlan) ? $ratePlan->min_nights : '') }}" 
                   min="1"
                   placeholder="Leave empty for no minimum"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            <div class="form-help">Require guests to book a minimum number of nights</div>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary" id="submitBtn">
        
        <span id="btnText">{{ isset($ratePlan) ? 'Update Rate Plan' : 'Create Rate Plan' }}</span>
        <span id="btnLoading" style="display: none;">{{ isset($ratePlan) ? 'Updating...' : 'Creating...' }}</span>
    </button>
</form>

<script>
// Store room type base prices
const roomTypeBasePrices = {
    @foreach($allRoomTypes as $roomType)
        {{ $roomType->id }}: {{ $roomType->base_price }},
    @endforeach
};

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

// Validate price against base price
function validatePrice() {
    const priceInput = document.getElementById('price_per_night');
    const roomTypeId = document.getElementById('room_type_id').value;
    const price = parseFloat(priceInput.value);
    const errorElement = document.getElementById('price_per_night-error');
    
    if (roomTypeId && roomTypeBasePrices[roomTypeId]) {
        const basePrice = parseFloat(roomTypeBasePrices[roomTypeId]);
        
        if (price < basePrice) {
            priceInput.classList.add('error');
            priceInput.style.borderColor = '#dc3545';
            if (errorElement) {
                errorElement.textContent = 'Price must be at least $' + basePrice.toFixed(2) + ' (base price)';
                errorElement.classList.add('show');
            }
            return false;
        } else {
            priceInput.classList.remove('error');
            priceInput.style.borderColor = '';
            if (errorElement) {
                errorElement.classList.remove('show');
            }
            return true;
        }
    }
    return true;
}

// Update minimum price and helper text when room type changes
function updatePriceConstraints() {
    const roomTypeId = document.getElementById('room_type_id').value;
    const priceInput = document.getElementById('price_per_night');
    const formHelp = priceInput.parentElement.querySelector('.form-help');
    
    if (roomTypeId && roomTypeBasePrices[roomTypeId]) {
        const basePrice = roomTypeBasePrices[roomTypeId];
        priceInput.min = basePrice;
        priceInput.placeholder = parseFloat(basePrice).toFixed(2);
        
        if (formHelp) {
            formHelp.textContent = 'Must be at least $' + parseFloat(basePrice).toFixed(2) + ' (base price)';
        }
    }
    
    // Re-validate price if already entered
    if (priceInput.value) {
        validatePrice();
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
    
    // Add room type change listener
    const roomTypeSelect = document.getElementById('room_type_id');
    if (roomTypeSelect) {
        roomTypeSelect.addEventListener('change', updatePriceConstraints);
        
        // Initialize on page load if room type is already selected
        if (roomTypeSelect.value) {
            updatePriceConstraints();
        }
    }
    
    // Add price validation listener
    const priceInput = document.getElementById('price_per_night');
    if (priceInput) {
        priceInput.addEventListener('input', validatePrice);
        priceInput.addEventListener('blur', validatePrice);
    }
});

// Prevent multiple form submissions with flag
let isSubmitting = false;

const form = document.getElementById('ratePlanForm');
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
    
    // Validate price against base price
    if (!validatePrice()) {
        isValid = false;
    }
    
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
    
    // Submit the form using FormData
    const formData = new FormData(form);
    
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
        if (response.ok) {
            // Store success message in localStorage
            const isEdit = form.action.includes('/edit') || document.querySelector('input[name="_method"]');
            const message = isEdit ? 'Rate Plan updated successfully!' : 'Rate Plan created successfully!';
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
