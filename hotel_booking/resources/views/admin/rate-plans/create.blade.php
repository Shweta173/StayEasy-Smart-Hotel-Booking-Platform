@extends('admin.layout')

@section('title', 'Add Rate Plan')

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
    }

    .info-box-text {
        font-size: 14px;
        color: #075985;
        line-height: 1.6;
    }

    .price-preview {
        background: #dcfce7;
        border: 2px solid #16a34a;
        border-radius: 12px;
        padding: 20px;
        margin-top: 12px;
        display: none;
    }

    .price-preview.show {
        display: block;
    }

    .price-preview-title {
        font-size: 14px;
        color: #166534;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .price-preview-amount {
        font-size: 32px;
        font-weight: 700;
        color: #16a34a;
    }

    .price-preview-label {
        font-size: 14px;
        color: #166534;
        margin-top: 4px;
    }
</style>

<div class="form-header">
    <h1> Add Rate Plan</h1>
    <p>Create seasonal pricing, special offers, and minimum stay requirements</p>
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

<form action="{{ route('admin.rate-plans.store') }}" method="POST">
    @csrf
    
    <div class="form-card">
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
                <select id="room_type_id" name="room_type_id" required>
                    <option value="">Choose a room type...</option>
                    @foreach($allRoomTypes as $roomType)
                        <option value="{{ $roomType->id }}">
                            {{ $roomType->name }} - {{ $roomType->property->name }} ({{ $roomType->property->location }}) - Base: ${{ number_format($roomType->base_price, 2) }}/night
                            @if($roomType->property->status === 'draft')
                                [DRAFT]
                            @endif
                        </option>
                    @endforeach
                </select>
                <div class="form-help">All room types from all properties shown - Choose which room type this pricing applies to</div>
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
                           value="{{ old('start_date') }}" 
                           min="{{ date('Y-m-d') }}"
                           required>
                    <div class="form-help">First day of special pricing</div>
                </div>

                <div class="form-group">
                    <label for="end_date">
                        End Date <span class="required">*</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" 
                           value="{{ old('end_date') }}" 
                           min="{{ date('Y-m-d') }}"
                           required>
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
                       value="{{ old('price_per_night') }}" 
                       step="0.01" 
                       min="0"
                       placeholder="199.00"
                       required
                       onkeyup="updatePricePreview()">
                <div class="form-help">Set the nightly rate for this date range</div>
                
                <div id="pricePreview" class="price-preview">
                    <div class="price-preview-title">Rate Plan Price:</div>
                    <div class="price-preview-amount">$<span id="previewAmount">0.00</span></div>
                    <div class="price-preview-label">per night</div>
                </div>
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
                       value="{{ old('min_nights') }}" 
                       min="1"
                       placeholder="Leave empty for no minimum">
                <div class="form-help">Require guests to book a minimum number of nights (e.g., 2 nights for weekends, 3 for holidays)</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.rate-plans') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span>💾</span>
                <span id="btnText">Create Rate Plan</span>
                <span id="btnLoading" style="display: none;">Creating...</span>
            </button>
        </div>
    </div>
</form>

<script>
// Store room type base prices
const roomTypeBasePrices = {
    @foreach($allRoomTypes as $roomType)
        {{ $roomType->id }}: {{ $roomType->base_price }},
    @endforeach
};

// Update price preview and validate against base price
function updatePricePreview() {
    const price = document.getElementById('price_per_night').value;
    const preview = document.getElementById('pricePreview');
    const amount = document.getElementById('previewAmount');
    const priceInput = document.getElementById('price_per_night');
    const roomTypeId = document.getElementById('room_type_id').value;
    
    // Get base price for selected room type
    const basePrice = roomTypeBasePrices[roomTypeId];
    
    if (price && price > 0) {
        amount.textContent = parseFloat(price).toFixed(2);
        preview.classList.add('show');
        
        // Validate against base price
        if (roomTypeId && basePrice && parseFloat(price) < parseFloat(basePrice)) {
            priceInput.setCustomValidity('Price per night must be greater than or equal to the base price of $' + parseFloat(basePrice).toFixed(2));
            priceInput.style.borderColor = '#dc3545';
        } else {
            priceInput.setCustomValidity('');
            priceInput.style.borderColor = '';
        }
    } else {
        preview.classList.remove('show');
        priceInput.setCustomValidity('');
        priceInput.style.borderColor = '';
    }
}

// Update minimum price when room type changes
document.getElementById('room_type_id').addEventListener('change', function() {
    const roomTypeId = this.value;
    const priceInput = document.getElementById('price_per_night');
    
    if (roomTypeId && roomTypeBasePrices[roomTypeId]) {
        const basePrice = roomTypeBasePrices[roomTypeId];
        priceInput.min = basePrice;
        priceInput.placeholder = parseFloat(basePrice).toFixed(2);
        
        // Show helper text
        const formHelp = priceInput.nextElementSibling;
        if (formHelp && formHelp.classList.contains('form-help')) {
            formHelp.textContent = 'Must be at least $' + parseFloat(basePrice).toFixed(2) + ' (base price)';
        }
    }
    
    // Re-validate price if already entered
    if (priceInput.value) {
        updatePricePreview();
    }
});

// Prevent multiple form submissions
const form = document.querySelector('form');
const submitBtn = document.getElementById('submitBtn');
const btnText = document.getElementById('btnText');
const btnLoading = document.getElementById('btnLoading');

form.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.6';
    submitBtn.style.cursor = 'not-allowed';
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
});
</script>
@endsection
