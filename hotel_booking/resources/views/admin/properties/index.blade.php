@extends('admin.layout')

@section('title', 'Properties Management')
@section('page-title', 'Properties')

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

    .properties-header {
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

    .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 24px;
    }

    .property-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .property-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .property-image {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg,#003580,#00509e);
        position: relative;
        overflow: hidden;
    }

    /* All slides sit stacked; only .active is visible */
    .slide-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity .4s ease;
        pointer-events: none;
    }
    .slide-img.active {
        opacity: 1;
        pointer-events: auto;
    }

    /* Arrows */
    .slide-prev, .slide-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px; height: 34px;
        border-radius: 50%;
        border: none;
        background: rgba(0,0,0,.55);
        color: white;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 30;
        transition: background .2s;
        padding: 0;
    }
    .slide-prev:hover,.slide-next:hover { background: rgba(0,0,0,.9); }
    .slide-prev { left: 8px; }
    .slide-next { right: 8px; }
    .property-card:hover .slide-prev,
    .property-card:hover .slide-next { display: flex; }

    /* Dot indicators */
    .slide-dots {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 5px;
        z-index: 30;
    }
    .slide-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.5);
        border: none;
        padding: 0;
        cursor: pointer;
        transition: background .2s, transform .2s;
    }
    .slide-dot.active { background: #fff; transform: scale(1.4); }

    /* Counter */
    .slide-count {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0,0,0,.55);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 12px;
        z-index: 30;
    }

    .status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .status-published {
        background: rgba(22, 163, 74, 0.9);
        color: white;
    }

    .status-draft {
        background: rgba(245, 158, 11, 0.9);
        color: white;
    }

    .property-content {
        padding: 20px;
    }

    .property-name {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .property-location {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #6b6b6b;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .property-description {
        color: #6b6b6b;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 44px;
    }

    .property-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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

    .property-actions {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
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

    .btn-toggle {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-toggle:hover {
        background: #f59e0b;
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
    <form method="GET" action="{{ route('admin.properties') }}" class="search-form">
        <input 
            type="text" 
            name="search" 
            class="search-input" 
            placeholder="Search by name, location or address..." 
            value="{{ request('search') }}"
        >
        <select name="status" class="filter-select" style="max-width: 180px;">
            <option value="">All Statuses</option>
            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
        <button type="submit" class="btn-search">Search</button>
    </form>

    <button onclick="openSlidePanel('{{ route('admin.properties.create') }}', ' Add New Property')" class="btn-primary">
        <span>+</span>
        Add New Property
    </button>
</div>


@if($properties->count() > 0)
    <div class="properties-grid">
        @foreach($properties as $property)
        <div class="property-card">
                                  <div class="property-image" id="card-{{ $property->id }}">
                @php $imgs = $property->images; $total = $imgs->count(); @endphp

                @if($total === 0)
                    <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:56px;">🏨</div>
                @else
                    {{-- All images — first is active --}}
                    @foreach($imgs as $i => $img)
                        <img class="slide-img {{ $i === 0 ? 'active' : '' }}"
                             src="{{ asset($img->image_url) }}"
                             alt="{{ $property->name }}"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600';">
                    @endforeach

                    @if($total > 1)
                        <button class="slide-prev" onclick="slideCard({{ $property->id }},-1,event)">&#8249;</button>
                        <button class="slide-next" onclick="slideCard({{ $property->id }}, 1,event)">&#8250;</button>
                        <div class="slide-dots">
                            @foreach($imgs as $i => $img)
                                <button class="slide-dot {{ $i === 0 ? 'active' : '' }}"
                                        onclick="slideCardTo({{ $property->id }},{{ $i }},event)"></button>
                            @endforeach
                        </div>
                        <div class="slide-count">
                            <span id="cnt-{{ $property->id }}">1</span> / {{ $total }}
                        </div>
                    @endif
                @endif

                <span class="status-badge status-{{ $property->status }}">{{ $property->status }}</span>
            </div>
            <div class="property-content">
                <h3 class="property-name">{{ $property->name }}</h3>
                <div class="property-location">
                    📍 {{ $property->location }}
                </div>
                <p class="property-description">
                    {{ $property->description }}
                </p>
                <div class="property-stats">
                    <div class="stat-item">
                        <div class="stat-label">Rooms</div>
                        <div class="stat-value">{{ $property->roomTypes->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Photos</div>
                        <div class="stat-value">{{ $property->images->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Units</div>
                        <div class="stat-value">{{ $property->roomTypes->sum('total_units') }}</div>
                    </div>
                </div>
                <div class="property-actions">
                    <button onclick="openSlidePanel('{{ route('admin.properties.edit', $property->id) }}', 'Edit Property')" class="btn-action btn-edit">
                        Edit
                    </button>
                    <button type="button" 
                            class="btn-action btn-toggle" 
                            onclick="togglePropertyStatus({{ $property->id }}, '{{ $property->status }}')"
                            id="toggleBtn{{ $property->id }}">
                        @if($property->status === 'published')
                            Draft
                        @else
                            Publish
                        @endif
                    </button>
                    <form action="{{ route('admin.properties.delete', $property->id) }}" method="POST" 
                          id="deleteForm{{ $property->id }}" style="margin: 0;">
                        @csrf
                        <button type="button" class="btn-action btn-delete" style="width: 100%;" 
                                onclick="showConfirmDialog('Delete Property', 'Are you sure you want to delete {{ $property->name }}? This action cannot be undone.', function() { document.getElementById('deleteForm{{ $property->id }}').submit(); })">
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
            Showing {{ $properties->firstItem() ?? 0 }} to {{ $properties->lastItem() ?? 0 }} of {{ $properties->total() }} entries
        </div>
        {{ $properties->links('vendor.pagination.custom') }}
    </div>
@else
    <div class="no-data">
        
        <div class="no-data-text">No properties found</div>
    </div>
@endif
@endsection

<script>
function slideCardTo(id, idx, e) {
    if (e) e.stopPropagation();
    var wrap = document.getElementById('card-' + id);
    wrap.querySelectorAll('.slide-img').forEach(function(img, i) {
        img.classList.toggle('active', i === idx);
    });
    wrap.querySelectorAll('.slide-dot').forEach(function(dot, i) {
        dot.classList.toggle('active', i === idx);
    });
    var cnt = document.getElementById('cnt-' + id);
    if (cnt) cnt.textContent = idx + 1;
}

function slideCard(id, dir, e) {
    if (e) e.stopPropagation();
    var wrap = document.getElementById('card-' + id);
    var imgs = wrap.querySelectorAll('.slide-img');
    var cur = 0;
    imgs.forEach(function(img, i) { if (img.classList.contains('active')) cur = i; });
    slideCardTo(id, (cur + dir + imgs.length) % imgs.length, null);
}

function togglePropertyStatus(propertyId, currentStatus) {
    const btn = document.getElementById('toggleBtn' + propertyId);
    const badge = btn.closest('.property-card').querySelector('.status-badge');
    
    // Disable button during request
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    fetch(`/admin/properties/${propertyId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Check if msgtype is success (not data.success)
        if (data.msgtype === 'success' && data.data && data.data.status) {
            // Update button text and badge
            const newStatus = data.data.status;
            btn.textContent = newStatus === 'published' ? 'Draft' : 'Publish';
            badge.textContent = newStatus;
            badge.className = 'status-badge status-' + newStatus;
            
            // Show toast notification
            showToast(data.msg || 'Property status updated successfully!', 'success');
        } else {
            showToast(data.msg || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating status', 'error');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}
</script>
