@extends('admin.layout')

@section('title', 'Staff & Users')

@section('page-title', 'Staff & Users')

@section('content')
<style>
    /* Add Staff Button */
    .btn-add-staff-top {
        float: right;
        margin-bottom: 24px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0, 53, 128, 0.3);
    }

    .btn-add-staff-top:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.4);
        transform: translateY(-1px);
    }

    .btn-add-staff-top .plus-icon {
        font-size: 18px;
        font-weight: 700;
    }

    /* Staff Grid */
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .staff-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.2s ease;
        position: relative;
    }

    .staff-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .staff-header {
        padding: 24px;
        border-bottom: 1px solid #f0f0f0;
    }

    .staff-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: 16px;
    }

    .staff-avatar.admin {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .staff-avatar.manager {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .staff-avatar.front_desk {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .staff-name {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .staff-email {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .staff-role {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .staff-role.admin {
        background: #fef3f2;
        color: #d92d20;
    }

    .staff-role.manager {
        background: #eff8ff;
        color: #1570ef;
    }

    .staff-role.front_desk {
        background: #f0fdf4;
        color: #15803d;
    }

    .staff-body {
        padding: 20px 24px;
    }

    .staff-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f9fafb;
    }

    .staff-info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 600;
    }

    .info-value {
        font-size: 13px;
        color: #1f2937;
        font-weight: 600;
    }

    .staff-footer {
        padding: 16px 24px;
        background: #f9fafb;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-sm {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #eff8ff;
        color: #1570ef;
    }

    .btn-edit:hover {
        background: #d1e9ff;
    }

    .btn-delete {
        background: #fef3f2;
        color: #d92d20;
    }

    .btn-delete:hover {
        background: #fee2e2;
    }

    /* Add Staff Button - Top Right */
    .btn-add-staff-top {
        padding: 10px 20px;
        background: #0071c2;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 113, 194, 0.3);
    }

    .btn-add-staff-top:hover {
        background: #005999;
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.4);
        transform: translateY(-1px);
    }

    /* Slide Panel Modal */
    .staff-panel-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .staff-panel-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .staff-slide-panel {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 20px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .staff-slide-panel.active {
        right: 0;
    }

    .modal-header {
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .btn-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .modal-body {
        padding: 24px;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #dc2626;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .form-input.error {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    .form-input.error:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }

    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        max-height: 45px;
    }

    .form-select:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .form-select.error {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    .form-select.error:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }

    .form-help {
        font-size: 13px;
        color: #6b6b6b;
        margin-top: 6px;
    }

    .form-error {
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
        display: none;
    }

    .form-error.show {
        display: block;
    }

    .modal-footer {
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #4b5563;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
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
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
    }

    .empty-text {
        font-size: 16px;
        color: #6b7280;
        margin-bottom: 24px;
    }

    /* Role Permissions Section */
    .role-permissions {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        margin-top: 8px;
    }

    .permission-item {
        font-size: 12px;
        color: #4b5563;
        padding: 6px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .permission-item::before {
        content: "✓";
        color: #10b981;
        font-weight: 700;
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 20px 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .stat-number {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .stat-number.admin { color: #d92d20; }
    .stat-number.manager { color: #1570ef; }
    .stat-number.front_desk { color: #15803d; }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<!-- Add Staff Button - Top Right -->
<button class="btn-add-staff-top" onclick="openAddModal()">
    <span class="plus-icon">+</span>
    <span>Add Staff</span>
</button>

<div style="clear: both;"></div>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-number admin">{{ $stats['admin'] }}</div>
        <div class="stat-label">Administrators</div>
    </div>
    <div class="stat-card">
        <div class="stat-number manager">{{ $stats['manager'] }}</div>
        <div class="stat-label">Managers</div>
    </div>
    <div class="stat-card">
        <div class="stat-number front_desk">{{ $stats['front_desk'] }}</div>
        <div class="stat-label">Front Desk Staff</div>
    </div>
</div>

<!-- Staff Grid -->
@if($staffMembers->count() > 0)
<div class="staff-grid">
    @foreach($staffMembers as $staff)
    <div class="staff-card">
        <div class="staff-header">
            <div class="staff-avatar {{ $staff->role }}">
                {{ strtoupper(substr($staff->name, 0, 2)) }}
            </div>
            <div class="staff-name">{{ $staff->name }}</div>
            <div class="staff-email">{{ $staff->email }}</div>
            <span class="staff-role {{ $staff->role }}">
                {{ ucwords(str_replace('_', ' ', $staff->role)) }}
            </span>
        </div>
        
        <div class="staff-body">
            <div class="staff-info-row">
                <span class="info-label">Member Since</span>
                <span class="info-value">{{ $staff->created_at->format('M d, Y') }}</span>
            </div>
            <div class="staff-info-row">
                <span class="info-label">Last Updated</span>
                <span class="info-value">{{ $staff->updated_at->diffForHumans() }}</span>
            </div>
        </div>
        
        <div class="staff-footer">
            <button class="btn-sm btn-edit" onclick="editStaff({{ $staff->id }}, '{{ $staff->name }}', '{{ $staff->email }}', '{{ $staff->role }}')">
                Edit
            </button>
            @if($staff->id != session('admin_user')['id'])
            <button class="btn-sm btn-delete" onclick="deleteStaff({{ $staff->id }}, '{{ $staff->name }}')">
                Delete
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <div class="empty-icon">👥</div>
    <h2 class="empty-title">No Staff Members Yet</h2>
    <p class="empty-text">Add your first staff member to get started</p>
</div>
@endif

<!-- Add/Edit Staff Slide Panel -->
<div class="staff-panel-overlay" id="staffPanelOverlay"></div>
<div class="staff-slide-panel" id="staffSlidePanel">
    <div class="modal-header">
        <h2 class="modal-title" id="modalTitle">Add New Staff Member</h2>
        <button class="btn-close" onclick="closeStaffPanel()">&times;</button>
    </div>
    
    <form id="staffForm" method="POST" onsubmit="submitStaffForm(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
        @csrf
        <input type="hidden" id="staffId" name="staff_id">
        <input type="hidden" id="formMethod" name="_method" value="POST">
        
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">
                    Full Name <span class="required">*</span>
                </label>
                <input type="text" id="staffName" name="name" class="form-input" placeholder="John Doe" required>
                <div class="form-error" id="staffName-error">This is a required field.</div>
                <div class="form-help">Enter the staff member's full name</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    Email Address <span class="required">*</span>
                </label>
                <input type="email" id="staffEmail" name="email" class="form-input" placeholder="john@hotel.com" required>
                <div class="form-error" id="staffEmail-error">This is a required field.</div>
                <div class="form-help">This will be used for login</div>
            </div>
            
            <div class="form-group" id="passwordGroup">
                <label class="form-label">
                    Password <span class="required">*</span>
                </label>
                <input type="password" id="staffPassword" name="password" class="form-input" placeholder="••••••••" required>
                <div class="form-error" id="staffPassword-error">This is a required field.</div>
                <div class="form-help">Minimum 8 characters</div>
            </div>
            
            <div class="form-group" id="passwordConfirmGroup">
                <label class="form-label">
                    Confirm Password <span class="required">*</span>
                </label>
                <input type="password" id="staffPasswordConfirmation" name="password_confirmation" class="form-input" placeholder="••••••••" required>
                <div class="form-error" id="staffPasswordConfirmation-error">This is a required field.</div>
                <div class="form-help">Re-enter the password to confirm</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    Role <span class="required">*</span>
                </label>
                <select id="staffRole" name="role" class="form-select" required onchange="updateRolePermissions()">
                    <option value="">Select Role</option>
                    <option value="admin">Administrator</option>
                    <option value="manager">Manager</option>
                    <option value="front_desk">Front Desk</option>
                </select>
                <div class="form-error" id="staffRole-error">Please select a role.</div>
                <div class="form-help">Select the access level for this staff member</div>
                
                <div class="role-permissions" id="rolePermissions" style="display: none;">
                    <div id="permissionsList"></div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeStaffPanel()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitBtn">Add Staff Member</button>
        </div>
    </form>
</div>

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

    // Role Permissions
    const rolePermissions = {
        admin: [
            'Full system access',
            'Manage staff and users',
            'Manage properties and rooms',
            'Manage bookings and payments',
            'View all reports and analytics',
            'System configuration'
        ],
        manager: [
            'Manage properties and rooms',
            'Manage rate plans and pricing',
            'View bookings and modify',
            'View financial reports',
            'Manage availability calendar'
        ],
        front_desk: [
            'View bookings',
            'Check-in and check-out guests',
            'View guest information',
            'Create new bookings',
            'Basic reporting access'
        ]
    };

    function updateRolePermissions() {
        const role = document.getElementById('staffRole').value;
        const permissionsContainer = document.getElementById('rolePermissions');
        const permissionsList = document.getElementById('permissionsList');
        
        if (role && rolePermissions[role]) {
            permissionsList.innerHTML = rolePermissions[role]
                .map(perm => `<div class="permission-item">${perm}</div>`)
                .join('');
            permissionsContainer.style.display = 'block';
        } else {
            permissionsContainer.style.display = 'none';
        }
    }

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Staff Member';
        document.getElementById('staffForm').reset();
        document.getElementById('staffId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('submitBtn').textContent = 'Add Staff Member';
        document.getElementById('passwordGroup').style.display = 'block';
        document.getElementById('passwordConfirmGroup').style.display = 'block';
        document.getElementById('staffPassword').required = true;
        document.getElementById('staffPasswordConfirmation').required = true;
        document.getElementById('rolePermissions').style.display = 'none';
        
        // Clear all validation errors
        const errorElements = document.querySelectorAll('.form-error');
        errorElements.forEach(el => el.classList.remove('show'));
        const inputElements = document.querySelectorAll('.form-input, .form-select');
        inputElements.forEach(el => el.classList.remove('error'));
        
        // Open slide panel
        document.getElementById('staffPanelOverlay').classList.add('active');
        document.getElementById('staffSlidePanel').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function editStaff(id, name, email, role) {
        document.getElementById('modalTitle').textContent = 'Edit Staff Member';
        document.getElementById('staffId').value = id;
        document.getElementById('staffName').value = name;
        document.getElementById('staffEmail').value = email;
        document.getElementById('staffRole').value = role;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('submitBtn').textContent = 'Update Staff Member';
        document.getElementById('passwordGroup').style.display = 'none';
        document.getElementById('passwordConfirmGroup').style.display = 'none';
        document.getElementById('staffPassword').required = false;
        document.getElementById('staffPasswordConfirmation').required = false;
        
        // Clear all validation errors
        const errorElements = document.querySelectorAll('.form-error');
        errorElements.forEach(el => el.classList.remove('show'));
        const inputElements = document.querySelectorAll('.form-input, .form-select');
        inputElements.forEach(el => el.classList.remove('error'));
        
        updateRolePermissions();
        
        // Open slide panel
        document.getElementById('staffPanelOverlay').classList.add('active');
        document.getElementById('staffSlidePanel').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeStaffPanel() {
        document.getElementById('staffPanelOverlay').classList.remove('active');
        document.getElementById('staffSlidePanel').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Prevent multiple form submissions with flag
    let isSubmitting = false;

    function submitStaffForm(event) {
        event.preventDefault();
        
        // Clear all previous validation errors
        clearValidationErrors();
        
        // Validate all required fields
        const form = document.getElementById('staffForm');
        const requiredFields = Array.from(form.querySelectorAll('[required]'));
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
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
        submitBtn.style.cursor = 'not-allowed';
        const originalText = submitBtn.textContent;
        submitBtn.textContent = submitBtn.textContent.includes('Add') ? 'Adding...' : 'Updating...';
        
        const formData = new FormData(form);
        const staffId = document.getElementById('staffId').value;
        
        // If editing (has staffId) and password fields are hidden, remove them from FormData
        if (staffId && document.getElementById('passwordGroup').style.display === 'none') {
            formData.delete('password');
            formData.delete('password_confirmation');
        }
        
        let url = staffId ? `/admin/staff/${staffId}` : '/admin/staff';
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            return response.json().then(data => ({
                status: response.status,
                ok: response.ok,
                data: data
            }));
        })
        .then(({status, ok, data}) => {
            if (ok && (data.msgtype === 'success' || data.success)) {
                closeStaffPanel();
                showToast(data.msg || data.message || 'Staff member saved successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else if (status === 422 && data.errors) {
                // Handle Laravel validation errors
                showValidationErrors(data.errors);
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.textContent = originalText;
            } else {
                // Show general error message inline
                showGeneralError(data.msg || data.message || 'An error occurred while saving');
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showGeneralError('An error occurred while saving staff member');
            isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            submitBtn.textContent = originalText;
        });
    }

    function clearValidationErrors() {
        // Clear all error messages
        document.querySelectorAll('.form-error').forEach(el => el.classList.remove('show'));
        // Clear error styling from inputs
        document.querySelectorAll('.form-input, .form-select').forEach(el => el.classList.remove('error'));
    }

    function showValidationErrors(errors) {
        // errors is an object like: { name: ["error1"], email: ["error2"], password: ["error"] }
        for (const [field, messages] of Object.entries(errors)) {
            let fieldName = field;
            
            // Handle password_confirmation field mapping
            if (field === 'password_confirmation') {
                fieldName = 'passwordConfirmation';
            } else {
                // Capitalize first letter for other fields
                fieldName = field.charAt(0).toUpperCase() + field.slice(1);
            }
            
            const input = document.getElementById('staff' + fieldName);
            const errorDiv = document.getElementById('staff' + fieldName + '-error');
            
            if (input && errorDiv) {
                input.classList.add('error');
                errorDiv.textContent = messages[0]; // Show first error message
                errorDiv.classList.add('show');
            }
        }
    }

    function showGeneralError(message) {
        // Show error on the first field as a fallback
        const firstInput = document.querySelector('.form-input');
        const firstError = document.querySelector('.form-error');
        
        if (firstInput && firstError) {
            firstInput.classList.add('error');
            firstError.textContent = message;
            firstError.classList.add('show');
        }
    }

    function deleteStaff(id, name) {
        showConfirmDialog(
            'Delete Staff Member',
            `Are you sure you want to delete ${name}? This action cannot be undone.`,
            function() {
                // User confirmed, proceed with deletion
                fetch(`/admin/staff/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    return response.json().then(data => ({
                        status: response.status,
                        ok: response.ok,
                        data: data
                    }));
                })
                .then(({status, ok, data}) => {
                    if (ok && (data.msgtype === 'success' || data.success)) {
                        showToast(data.msg || data.message || 'Staff member deleted successfully', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.msg || data.message || 'An error occurred', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while deleting staff member', 'error');
                });
            }
        );
    }

    // Panel only closes via close button or cancel button, not by clicking overlay
</script>

@endsection
