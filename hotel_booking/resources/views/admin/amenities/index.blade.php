@extends('admin.layout')

@section('title', 'Amenities')
@section('page-title', 'Amenities')

@section('content')
<style>
    /* Header with Filters and Add Button */
    .page-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .filters-container {
        display: flex;
        gap: 12px;
        align-items: center;
        flex: 1;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 200px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input {
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all .2s;
        background: white;
        height: 38px;
    }

    .filter-input:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .btn-filter {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border: none;
        padding: 0 24px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        height: 38px;
        margin-top: 18px;
    }

    .btn-filter:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .btn-primary {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        flex-shrink: 0;
        width: auto;
        min-width: auto;
        max-width: fit-content;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border-left: 4px solid #16a34a;
    }

    .amenities-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #0071c2 0%, #003580 100%);
        color: white;
    }

    thead th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid #e0e0e0;
        transition: background 0.2s ease;
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    tbody td {
        padding: 16px 20px;
        color: #262626;
        font-size: 14px;
    }

    .amenity-name {
        font-weight: 600;
        font-size: 16px;
        color: #262626;
    }

    .icon-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        font-family: monospace;
    }

    .no-icon {
        color: #9ca3af;
        font-style: italic;
    }

    .usage-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #e0f2fe;
        color: #0369a1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-edit {
        background: #e0f2fe;
        color: #0369a1;
    }

    .btn-edit:hover {
        background: #0369a1;
        color: white;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }

    .no-data {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .no-data-icon {
        font-size: 72px;
        margin-bottom: 16px;
    }

    .no-data-text {
        color: #6b6b6b;
        font-size: 18px;
        margin-bottom: 24px;
    }

    /* Form Styles for Slide Panel */
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

    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-group input:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .form-group input.error {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    .form-group input.error:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }

    .form-error {
        color: #dc2626;
        font-size: 13px;
        margin-top: 6px;
        display: none;
        font-weight: 500;
    }

    .form-error.show {
        display: block;
    }

    .form-help {
        font-size: 13px;
        color: #6b6b6b;
        margin-top: 6px;
    }

    .btn-submit {
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        width: 100%;
        justify-content: center;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<!-- Header with Filters and Add Button -->
<div class="page-header-actions">
    <form method="GET" action="{{ route('admin.amenities') }}" class="filters-container">
        <div class="filter-group">
            <label class="filter-label">Search</label>
            <input 
                type="text" 
                name="search" 
                class="filter-input" 
                placeholder="Search by name or icon..." 
                value="{{ request('search') }}"
            >
        </div>
        <button type="submit" class="btn-filter">
            Filter
        </button>
    </form>

    <button onclick="openAddAmenityPanel()" class="btn-primary">
        <span>+</span>
        Add Amenity
    </button>
</div>

@if($amenities->count() > 0)
    <div class="amenities-table">
        <table>
            <thead>
                <tr>
                    <th>Amenity Name</th>
                    <th>Icon</th>
                    <th>Room Types</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($amenities as $amenity)
                <tr>
                    <td>
                        <div class="amenity-name">{{ $amenity->name }}</div>
                    </td>
                    <td>
                        @if($amenity->icon)
                            <span class="icon-badge">{{ $amenity->icon }}</span>
                        @else
                            <span class="no-icon">No icon</span>
                        @endif
                    </td>
                    <td>
                        <div class="usage-badge">
                            🏨 {{ $amenity->room_types_count }} {{ $amenity->room_types_count === 1 ? 'room type' : 'room types' }}
                        </div>
                    </td>
                    <td>
                        {{ $amenity->created_at->format('M d, Y') }}
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="openEditAmenityPanel({{ $amenity->id }}, '{{ addslashes($amenity->name) }}', '{{ $amenity->icon }}')" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button type="button" class="btn-action btn-delete" 
                                    onclick="deleteAmenity({{ $amenity->id }}, '{{ addslashes($amenity->name) }}', {{ $amenity->room_types_count }})">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing {{ $amenities->firstItem() ?? 0 }} to {{ $amenities->lastItem() ?? 0 }} of {{ $amenities->total() }} entries
        </div>
        {{ $amenities->links('vendor.pagination.custom') }}
    </div>
@else
    <div class="no-data">
        🏨
        <div class="no-data-text">No amenities found</div>
        <p style="color: #6b6b6b; margin-bottom: 24px;">Create amenities like WiFi, Pool, Parking to assign to your room types</p>
        <button onclick="openAddAmenityPanel()" class="btn-primary">
            + Add Your First Amenity
        </button>
    </div>
@endif

<script>
// Open Add Amenity Panel
function openAddAmenityPanel() {
    const overlay = document.getElementById('panelOverlay');
    const panel = document.getElementById('slidePanel');
    const panelTitle = document.getElementById('panelTitle');
    const panelBody = document.getElementById('panelBody');
    
    panelTitle.textContent = 'Add New Amenity';
    panelBody.innerHTML = `
        <form id="amenityForm" onsubmit="submitAmenity(event, 'create')">
            <div class="form-group">
                <label>
                    Amenity Name <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name"
                    id="name"
                    placeholder="e.g., Free WiFi, Swimming Pool"
                    required
                    maxlength="100"
                >
                <div class="form-error" id="name-error"></div>
                <div class="form-help">Max 100 characters</div>
            </div>

            <div class="form-group">
                <label>Icon Name (Optional)</label>
                <input 
                    type="text" 
                    name="icon"
                    id="icon"
                    placeholder="e.g., wifi, pool, parking"
                    maxlength="50"
                >
                <div class="form-error" id="icon-error"></div>
                <div class="form-help">
                    Common icons: wifi, pool, parking, restaurant, gym, spa, bar, breakfast, ac, tv, safe, minibar
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="btnText">Create Amenity</span>
                <span id="btnLoading" style="display: none;">Creating...</span>
            </button>
        </form>
    `;
    
    // Show panel
    overlay.classList.add('active');
    panel.classList.add('active');
}

// Open Edit Amenity Panel
function openEditAmenityPanel(id, name, icon) {
    const overlay = document.getElementById('panelOverlay');
    const panel = document.getElementById('slidePanel');
    const panelTitle = document.getElementById('panelTitle');
    const panelBody = document.getElementById('panelBody');
    
    panelTitle.textContent = 'Edit Amenity';
    panelBody.innerHTML = `
        <form id="amenityForm" onsubmit="submitAmenity(event, 'update', ${id})">
            <div class="form-group">
                <label>
                    Amenity Name <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name"
                    id="name"
                    value="${name}"
                    required
                    maxlength="100"
                >
                <div class="form-error" id="name-error"></div>
            </div>

            <div class="form-group">
                <label>Icon Name (Optional)</label>
                <input 
                    type="text" 
                    name="icon"
                    id="icon"
                    value="${icon || ''}"
                    maxlength="50"
                >
                <div class="form-error" id="icon-error"></div>
                <div class="form-help">
                    Common icons: wifi, pool, parking, restaurant, gym, spa, bar, breakfast, ac, tv, safe, minibar
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="btnText">Update Amenity</span>
                <span id="btnLoading" style="display: none;">Updating...</span>
            </button>
        </form>
    `;
    
    // Show panel
    overlay.classList.add('active');
    panel.classList.add('active');
}

// Clear validation errors
function clearErrors() {
    document.querySelectorAll('.form-error').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
    });
    document.querySelectorAll('input').forEach(el => {
        el.classList.remove('error');
    });
}

// Show validation error
function showError(fieldName, message) {
    const input = document.getElementById(fieldName);
    const errorEl = document.getElementById(fieldName + '-error');
    
    if (input) input.classList.add('error');
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
}

// Submit Amenity (Create or Update)
async function submitAmenity(event, mode, id = null) {
    event.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    // Disable button
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    
    const url = mode === 'create' 
        ? '{{ route("admin.amenities.store") }}'
        : '{{ route("admin.amenities.update", ["id" => ":id"]) }}'.replace(':id', id);
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            
            // Check if response was successful (200-299 status codes or msgtype === 'success')
            if (response.ok || data.msgtype === 'success') {
                // Check if no changes were made
                if (data.no_changes) {
                    // Show info toast and close panel without reload
                    closeSlidePanel();
                    showToast(data.msg || data.message, 'info');
                } else {
                    // Changes were made, reload page with success message
                    localStorage.setItem('toastMessage', data.msg || data.message || 'Operation successful');
                    localStorage.setItem('toastType', 'success');
                    closeSlidePanel();
                    window.location.reload();
                }
            } else {
                // Handle validation errors
                if (data.errors) {
                    // Laravel validation errors format
                    Object.keys(data.errors).forEach(fieldName => {
                        const messages = data.errors[fieldName];
                        if (messages && messages.length > 0) {
                            showError(fieldName, messages[0]);
                        }
                    });
                } else {
                    showToast(data.msg || data.message || 'Operation failed', 'error');
                }
                
                // Re-enable button
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }
        } else {
            closeSlidePanel();
            window.location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    }
}

// Delete Amenity
function deleteAmenity(id, name, roomTypesCount) {
    let message = `Are you sure you want to delete "${name}"?`;
    
    if (roomTypesCount > 0) {
        message += ` This amenity is used by ${roomTypesCount} room type(s) and will be removed from them.`;
    }
    
    showConfirmDialog(
        'Delete Amenity',
        message,
        async function() {
            try {
                const response = await fetch('{{ route("admin.amenities.delete", ["id" => ":id"]) }}'.replace(':id', id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    
                    // Check if response was successful (200-299 status codes or msgtype === 'success')
                    if (response.ok || data.msgtype === 'success') {
                        localStorage.setItem('toastMessage', data.msg || data.message || 'Amenity deleted successfully');
                        localStorage.setItem('toastType', 'success');
                        window.location.reload();
                    } else {
                        showToast(data.msg || data.message || 'Failed to delete amenity', 'error');
                    }
                } else {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            }
        }
    );
}
</script>
@endsection
