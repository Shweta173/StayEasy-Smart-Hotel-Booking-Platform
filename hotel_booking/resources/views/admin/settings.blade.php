@extends('admin.layout')

@section('title', 'Settings')

@section('page-title', 'Tax Settings')

@section('content')
<style>
    /* Include Settings object for JavaScript */
    .admin-bookings-page {
        --vat-display-mode: {{ $settings->prices_include_vat ? 'hidden' : 'visible' }};
    }
    
    .settings-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 32px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 8px;
    }

    .section-description {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 32px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 24px;
    }

    .form-row.single {
        grid-template-columns: 1fr;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #262626;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .form-help {
        font-size: 13px;
        color: #6b6b6b;
        margin-top: 6px;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #d1d5db;
        transition: 0.3s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #0071c2;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .toggle-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .toggle-info {
        flex: 1;
    }

    .toggle-label {
        font-weight: 600;
        color: #262626;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .toggle-description {
        font-size: 13px;
        color: #6b7280;
    }

    .input-group {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .input-addon {
        padding: 12px 16px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-left: none;
        border-radius: 0 8px 8px 0;
        font-weight: 600;
        color: #4b5563;
        min-width: 60px;
        text-align: center;
    }

    .input-group input {
        border-radius: 8px 0 0 8px !important;
    }

    .settings-footer {
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
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
    }

    .btn-primary {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
</style>

<div class="settings-container">
    <h2 class="section-title">VAT & Tax Configuration</h2>
    <p class="section-description">Configuration table storing the hotel's tax details, used by the pricing engine and the invoice generator. Managed from the CMS Settings page.</p>

    <form id="settingsForm" onsubmit="saveSettings(event)">
        @csrf

        <div class="toggle-group">
            <div class="toggle-info">
                <div class="toggle-label">VAT Registered</div>
                <div class="toggle-description">Is the hotel VAT-registered? If false, skip all VAT logic</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="vat_registered" value="1" 
                       {{ $settings->vat_registered ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>VAT Number</label>
                <input type="text" name="vat_number" value="{{ $settings->vat_number }}" 
                       placeholder="GB 123 4567 89" maxlength="30">
                <p class="form-help">e.g. GB 123 4567 89 — printed on invoices</p>
            </div>

            <div class="form-group">
                <label>Default VAT Rate</label>
                <div class="input-group">
                    <input type="number" name="default_vat_rate" value="{{ $settings->default_vat_rate }}" 
                           step="0.01" min="0" max="100" placeholder="20.00">
                    <span class="input-addon">%</span>
                </div>
                <p class="form-help">20.00 for standard UK rate</p>
            </div>
        </div>

        <div class="form-row single">
            <div class="form-group">
                <label>Hotel Legal Name</label>
                <input type="text" name="hotel_legal_name" value="{{ $settings->hotel_legal_name }}" 
                       placeholder="Grand Hotel Limited" maxlength="200">
                <p class="form-help">Registered business name for invoices</p>
            </div>
        </div>

        <div class="form-row single">
            <div class="form-group">
                <label>Hotel Legal Address</label>
                <textarea name="hotel_legal_address" rows="3">{{ $settings->hotel_legal_address }}</textarea>
                <p class="form-help">Registered address for invoices</p>
            </div>
        </div>

        <div class="toggle-group">
            <div class="toggle-info">
                <div class="toggle-label">Prices Include VAT</div>
                <div class="toggle-description">Always true for UK (law requires it)</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="prices_include_vat" value="1" 
                       {{ $settings->prices_include_vat ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="settings-footer">
            <button type="submit" class="btn btn-primary" id="saveBtn">
                <span id="btnText">💾 Save Settings</span>
                <span id="btnLoading" style="display: none;">Saving...</span>
            </button>
        </div>
    </form>
</div>

<script>
    let isSaving = false;

    function saveSettings(event) {
        event.preventDefault();
        
        if (isSaving) {
            return false;
        }
        
        isSaving = true;
        
        const form = document.getElementById('settingsForm');
        const formData = new FormData(form);
        const saveBtn = document.getElementById('saveBtn');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');
        
        // Convert checkboxes to proper boolean values
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                formData.set(checkbox.name, '0');
            }
        });
        
        saveBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        
        fetch('{{ route("admin.settings.save") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Settings saved successfully', 'success');
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
            
            isSaving = false;
            saveBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while saving settings', 'error');
            
            isSaving = false;
            saveBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    }
</script>

@endsection
