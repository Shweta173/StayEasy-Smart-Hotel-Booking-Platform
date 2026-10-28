@extends('admin.layout')

@section('title', 'Rate Plans & Pricing')
@section('page-title', 'Rate Plans / Pricing')

@section('content')
<style>
    /* Filters Section - Match Orders Page Style */
    .filters-section {
        background:white;border-radius:12px;padding:24px;
        margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.1);
    }
    .filters-grid {
        display:grid;grid-template-columns:2fr 2fr 1fr auto;
        gap:12px;align-items:end;
    }
    .filter-group { display:flex;flex-direction:column;gap:6px; }
    .filter-label { font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.3px; }
    .filter-input,.filter-select {
        padding:10px 14px;border:2px solid #e5e7eb;border-radius:8px;
        font-size:14px;font-weight:500;transition:all .2s;background:white;
    }
    .filter-input:focus,.filter-select:focus {
        outline:none;border-color:#0071c2;box-shadow:0 0 0 3px rgba(0,113,194,.1);
    }
    .btn-filter {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        height: 42px;
        transition: all 0.2s ease;
        font-size: 13px;
    }
    .btn-filter:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .rate-plans-header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 24px;
    }

    .btn-primary {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 24px;
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

    .rate-plans-table {
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

    .property-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .property-name {
        font-weight: 600;
        color: #0071c2;
    }

    .room-name {
        font-size: 13px;
        color: #6b6b6b;
    }

    .date-range {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .date-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
    }

    .date-label {
        color: #6b6b6b;
        min-width: 40px;
    }

    .date-value {
        font-weight: 600;
        color: #262626;
    }

    .duration-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        background: #e0f2fe;
        color: #0369a1;
        margin-top: 4px;
    }

    .price-cell {
        font-size: 20px;
        font-weight: 700;
        color: #16a34a;
    }

    .price-currency {
        font-size: 14px;
        font-weight: 600;
    }

    .min-nights {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #fef3c7;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #92400e;
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

    .status-active {
        color: #16a34a;
        font-weight: 600;
    }

    .status-upcoming {
        color: #ea580c;
        font-weight: 600;
    }

    .status-expired {
        color: #6b6b6b;
        font-weight: 400;
        opacity: 0.7;
    }
</style>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="{{ route('admin.rate-plans') }}" class="filters-grid">
        <div class="filter-group">
            <label class="filter-label">Property</label>
            <select name="property" class="filter-select">
                <option value="">All Properties</option>
                @foreach($properties as $prop)
                    <option value="{{ $prop->id }}" {{ request('property') == $prop->id ? 'selected' : '' }}>
                        {{ $prop->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Room Type</label>
            <select name="room_type" class="filter-select">
                <option value="">All Room Types</option>
                @foreach($roomTypes as $room)
                    <option value="{{ $room->id }}" {{ request('room_type') == $room->id ? 'selected' : '' }}>
                        {{ $room->name }} ({{ $room->property->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select name="status" class="filter-select">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Filter</button>
    </form>
</div>

<div class="rate-plans-header">
    <button onclick="openSlidePanel('{{ route('admin.rate-plans.create') }}', ' Add Rate Plan')" class="btn-primary">
        <span>+</span>
        Add Rate Plan
    </button>
</div>


@if($ratePlans->count() > 0)
    <div class="rate-plans-table">
        <table>
            <thead>
                <tr>
                    <th>Property & Room Type</th>
                    <th>Date Range</th>
                    <th>Price / Night</th>
                    <th>Min. Nights</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratePlans as $plan)
                <tr>
                    <td>
                        <div class="property-info">
                            <div class="property-name">{{ $plan->roomType->property->name }}</div>
                            <div class="room-name">📍 {{ $plan->roomType->name }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="date-range">
                            <div class="date-item">
                                <span class="date-label">From:</span>
                                <span class="date-value">{{ $plan->start_date->format('M d, Y') }}</span>
                            </div>
                            <div class="date-item">
                                <span class="date-label">To:</span>
                                <span class="date-value">{{ $plan->end_date->format('M d, Y') }}</span>
                            </div>
                            <div class="duration-badge">
                                {{ $plan->start_date->diffInDays($plan->end_date) + 1 }} days
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="price-cell">
                            <span class="price-currency">$</span>{{ number_format($plan->price_per_night, 2) }}
                        </div>
                    </td>
                    <td>
                        @if($plan->min_nights)
                            <div class="min-nights">
                                
                                {{ $plan->min_nights }} {{ $plan->min_nights === 1 ? 'night' : 'nights' }}
                            </div>
                        @else
                            <span style="color: #6b6b6b;">No minimum</span>
                        @endif
                    </td>
                    <td>
                        @if($plan->start_date->isFuture())
                            <span class="status-upcoming">⏳ Upcoming</span>
                        @elseif($plan->end_date->isPast())
                            <span class="status-expired">⏹️ Expired</span>
                        @else
                            <span class="status-active">✓ Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="openSlidePanel('{{ route('admin.rate-plans.edit', $plan->id) }}', 'Edit Rate Plan')" class="btn-action btn-edit">
                                Edit
                            </button>
                            <button type="button" class="btn-action btn-delete" 
                                    onclick="deleteRatePlan({{ $plan->id }})">
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
            Showing {{ $ratePlans->firstItem() ?? 0 }} to {{ $ratePlans->lastItem() ?? 0 }} of {{ $ratePlans->total() }} entries
        </div>
        {{ $ratePlans->links('vendor.pagination.custom') }}
    </div>
@else
    <div class="no-data">
        
        <div class="no-data-text">No rate plans found</div>
        <p style="color: #6b6b6b; margin-bottom: 24px;">Create seasonal pricing, special offers, and minimum stay requirements</p>
    </div>
@endif

<script>
/**
 * Delete rate plan with confirmation
 */
function deleteRatePlan(id) {
    showConfirmDialog(
        'Delete Rate Plan',
        'Are you sure you want to delete this rate plan? This action cannot be undone.',
        function() {
            // Show loading state
            const deleteBtn = event.target;
            const originalText = deleteBtn.textContent;
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Deleting...';
            
            // Send delete request
            fetch(`/admin/rate-plans/${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.msg || data.message || 'Failed to delete rate plan');
                    });
                }
                return response.json();
            })
            .then(data => {
                // Show success message
                showToast(data.msg || data.message || 'Rate plan deleted successfully!', 'success');
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast(error.message || 'Failed to delete rate plan', 'error');
                
                // Reset button state
                deleteBtn.disabled = false;
                deleteBtn.textContent = originalText;
            });
        }
    );
}
</script>

@endsection
