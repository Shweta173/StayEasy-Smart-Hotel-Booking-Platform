@extends('admin.layout')

@section('title', 'Room Types')
@section('page-title', 'Room Types')

@section('content')
<style>
    /* Header with Search and Add Button on Same Line - Match Amenities Style */
    .page-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .search-form {
        display: flex;
        gap: 8px;
        flex: 1;
        max-width: 700px;
    }

    .search-input {
        flex: 1;
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all .2s;
        background: white;
        height: 38px;
    }

    .search-input:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .filter-select {
        padding: 8px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all .2s;
        background: white;
        height: 38px;
        min-width: 180px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .btn-search {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border: none;
        padding: 8px 20px;
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
    }

    .btn-search:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    .room-types-header {
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

    .room-types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 24px;
    }

    .room-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .room-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .room-header {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        padding: 24px;
        color: white;
    }

    .room-name {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .property-name {
        font-size: 14px;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .room-content {
        padding: 20px;
    }

    .room-description {
        color: #6b6b6b;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 44px;
    }

    .room-stats {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        padding: 16px 0;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 16px;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }

    .stat-label {
        font-size: 11px;
        color: #6b6b6b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #0071c2;
    }

    .stat-value.price {
        color: #16a34a;
        font-size: 24px;
    }

    .room-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .btn-action {
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
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
</style>

<!-- Header with Search and Add Button on Same Line -->
<div class="page-header-actions">
    <form method="GET" action="{{ route('admin.room-types.index') }}" class="search-form">
        <input 
            type="text" 
            name="search" 
            class="search-input" 
            placeholder="Search by name or description..." 
            value="{{ request('search') }}"
        >
        <select name="property" class="filter-select">
            <option value="">All Properties</option>
            @foreach($properties as $prop)
                <option value="{{ $prop->id }}" {{ request('property') == $prop->id ? 'selected' : '' }}>
                    {{ $prop->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn-search">Search</button>
    </form>

    <button onclick="openSlidePanel('{{ route('admin.room-types.create') }}', 'Add Room Type')" class="btn-primary">
        <span>+</span>
        Add Room Type
    </button>
</div>


@if($roomTypes->count() > 0)
    <div class="room-types-grid">
        @foreach($roomTypes as $roomType)
        <div class="room-card">
            <div class="room-header">
                <div class="room-name">{{ $roomType->name }}</div>
                <div class="property-name">
                    
                    {{ $roomType->property->name }}
                </div>
            </div>
            <div class="room-content">
                <p class="room-description">
                    {{ $roomType->description }}
                </p>
                <div class="room-stats">
                    <div class="stat-item">
                        <div class="stat-label">Base Price</div>
                        <div class="stat-value price">${{ number_format($roomType->base_price, 2) }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Max Guest</div>
                        <div class="stat-value">{{ $roomType->max_occupancy }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Units</div>
                        <div class="stat-value">{{ $roomType->total_units }}</div>
                    </div>
                </div>
                <div class="room-actions">
                    <button onclick="openSlidePanel('{{ route('admin.room-types.edit', $roomType->id) }}', 'Edit Room Type')" class="btn-action btn-edit">
                        Edit
                    </button>
                    <form action="{{ route('admin.room-types.delete', $roomType->id) }}" method="POST" 
                          id="deleteFormRoom{{ $roomType->id }}" style="margin: 0;">
                        @csrf
                        <button type="button" class="btn-action btn-delete" style="width: 100%;" 
                                onclick="showConfirmDialog('Delete Room Type', 'Are you sure you want to delete {{ $roomType->name }}? This will also delete all associated rate plans and bookings.', function() { document.getElementById('deleteFormRoom{{ $roomType->id }}').submit(); })">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing {{ $roomTypes->firstItem() ?? 0 }} to {{ $roomTypes->lastItem() ?? 0 }} of {{ $roomTypes->total() }} entries
        </div>
        {{ $roomTypes->links('vendor.pagination.custom') }}
    </div>
@else
    <div class="no-data">
        
        <div class="no-data-text">No room types found</div>
        <p style="color: #6b6b6b; margin-bottom: 24px;">Add room types to define different accommodation options for your properties</p>
    </div>
@endif
@endsection
