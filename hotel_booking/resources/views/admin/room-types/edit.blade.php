@extends('admin.layout')

@section('title', 'Edit Room Type')

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
</style>

<div class="form-header">
    <h1>✏️ Edit Room Type</h1>
    <p>Update room type information</p>
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

<form action="{{ route('admin.room-types.update', $roomType->id) }}" method="POST">
    @csrf
    
    <div class="form-card">
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
                        <option value="{{ $property->id }}" {{ old('property_id', $roomType->property_id) == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}@if($property->location) ({{ $property->location }})@endif
                            @if($property->status === 'draft')
                                - [DRAFT]
                            @endif
                        </option>
                    @endforeach
                </select>
                <div class="form-help">Select which property this room type belongs to (All properties shown including drafts)</div>
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
                       value="{{ old('name', $roomType->name) }}" 
                       placeholder="e.g., Standard Double Room"
                       required>
                <div class="form-help">Name that guests will see (e.g., Deluxe Suite, Standard Room)</div>
            </div>

            <div class="form-group">
                <label for="description">
                    Description <span class="required">*</span>
                </label>
                <textarea id="description" name="description" 
                          placeholder="Describe the room features, bed types, size, and amenities..."
                          required>{{ old('description', $roomType->description) }}</textarea>
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
                           value="{{ old('base_price', $roomType->base_price) }}" 
                           step="0.01" 
                           min="0"
                           placeholder="199.00"
                           required>
                    <div class="form-help">Standard nightly rate (can be overridden by rate plans)</div>
                </div>

                <div class="form-group">
                    <label for="max_occupancy">
                        Maximum Occupancy <span class="required">*</span>
                    </label>
                    <input type="number" id="max_occupancy" name="max_occupancy" 
                           value="{{ old('max_occupancy', $roomType->max_occupancy) }}" 
                           min="1"
                           placeholder="2"
                           required>
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
                       value="{{ old('total_units', $roomType->total_units) }}" 
                       min="1"
                       placeholder="10"
                       required>
                <div class="form-help">Total number of rooms of this type available at the property</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.room-types') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span>💾</span>
                <span id="btnText">Update Room Type</span>
                <span id="btnLoading" style="display: none;">Updating...</span>
            </button>
        </div>
    </div>
</form>

<script>
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
