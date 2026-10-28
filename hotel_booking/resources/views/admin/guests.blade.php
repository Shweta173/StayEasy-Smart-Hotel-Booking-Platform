@extends('admin.layout')

@section('title', 'Guests & Customers')
@section('page-title', 'Guests & Customers')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #0071c2;
    }

    .stat-card.stat-bookings { border-left-color: #16a34a; }
    .stat-card.stat-revenue { border-left-color: #f59e0b; }
    .stat-card.stat-repeat { border-left-color: #8b5cf6; }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #262626;
    }

    .stat-value.revenue {
        color: #16a34a;
    }

    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .filter-input,
    .filter-select {
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        background: white;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .btn-filter {
        padding: 10px 24px;
        background: #0071c2;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 42px;
    }

    .btn-filter:hover {
        background: #00509e;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
    }

    .guests-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .guests-table {
        width: 100%;
        border-collapse: collapse;
    }

    .guests-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .guests-table th {
        padding: 12px 14px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .guests-table td {
        padding: 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        color: #262626;
        vertical-align: middle;
    }

    .guests-table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .guests-table tbody tr:hover {
        background: #f9fafb;
        box-shadow: inset 0 0 0 2px #e3f2fd;
    }

    .guest-name {
        font-weight: 700;
        color: #0071c2;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .guest-email {
        font-size: 11px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 2px;
    }

    .guest-phone {
        font-size: 11px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .guest-since {
        font-size: 12px;
        color: #9ca3af;
    }

    .bookings-count {
        font-size: 24px;
        font-weight: 700;
        color: #16a34a;
        margin-bottom: 4px;
    }

    .bookings-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .revenue-value {
        font-size: 18px;
        font-weight: 700;
        color: #16a34a;
        font-family: 'Courier New', monospace;
        margin-bottom: 4px;
    }

    .revenue-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .last-booking {
        font-size: 12px;
        color: #262626;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .last-booking-date {
        font-size: 11px;
        color: #9ca3af;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-vip {
        background: #fef3c7;
        color: #a16207;
    }

    .badge-regular {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-new {
        background: #d1fae5;
        color: #065f46;
    }

    .btn-view {
        padding: 6px 14px;
        background: #0071c2;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-view:hover {
        background: #00509e;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
    }

    /* Right Side Modal */
    .side-modal {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        height: 100vh;
        background: white;
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
        transition: right 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }

    .side-modal.active {
        right: 0;
    }

    .side-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
    }

    .side-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .side-modal-header {
        padding: 24px;
        border-bottom: 2px solid #e5e7eb;
        background: #f9fafb;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .side-modal-title {
        font-size: 24px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 8px;
    }

    .side-modal-subtitle {
        font-size: 14px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .side-modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.2s ease;
    }

    .side-modal-close:hover {
        background: #fee2e2;
        border-color: #dc2626;
        color: #dc2626;
        transform: rotate(90deg);
    }

    .side-modal-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        padding: 24px;
        background: #f9fafb;
    }

    .modal-stat-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        border-left: 3px solid #0071c2;
    }

    .modal-stat-card.stat-revenue { border-left-color: #16a34a; }

    .modal-stat-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .modal-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #262626;
    }

    .modal-stat-value.revenue {
        color: #16a34a;
    }

    .side-modal-content {
        padding: 24px;
    }

    .booking-history-header {
        font-size: 18px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .booking-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .booking-card:hover {
        border-color: #0071c2;
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.1);
        transform: translateY(-2px);
    }

    .booking-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }

    .booking-ref-modal {
        font-size: 16px;
        font-weight: 700;
        color: #0071c2;
        margin-bottom: 4px;
    }

    .booking-date-modal {
        font-size: 11px;
        color: #9ca3af;
    }

    .booking-amount-modal {
        font-size: 18px;
        font-weight: 700;
        color: #16a34a;
        font-family: 'Courier New', monospace;
        margin-bottom: 4px;
    }

    .booking-card-body {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .booking-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #6b7280;
    }

    .booking-info-row .label {
        font-weight: 600;
        color: #262626;
        min-width: 80px;
    }

    .booking-property-name {
        font-weight: 700;
        color: #0071c2;
    }

    .no-bookings-modal {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .no-bookings-icon-modal {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.3;
    }

    .loading-spinner {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .spinner {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #0071c2;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .side-modal {
            width: 100%;
            right: -100%;
        }

        .side-modal-stats {
            grid-template-columns: 1fr;
        }
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .no-results-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .pagination-wrapper {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid #e5e7eb;
        background: #f9fafb;
    }

    .pagination-info {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Guests</div>
        <div class="stat-value">{{ $stats['total_guests'] }}</div>
    </div>
    <div class="stat-card stat-bookings">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value">{{ $stats['total_bookings'] }}</div>
    </div>
    <div class="stat-card stat-revenue">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value revenue">£{{ number_format($stats['total_revenue'], 2) }}</div>
    </div>
    <div class="stat-card stat-repeat">
        <div class="stat-label">Repeat Guests</div>
        <div class="stat-value">{{ $stats['repeat_guests'] }}</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="{{ route('admin.guests') }}" id="filterForm">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Search Guest</label>
                <input 
                    type="text" 
                    name="search" 
                    class="filter-input" 
                    placeholder="Name, email, phone..."
                    value="{{ request('search') }}"
                >
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Guest Type</label>
                <select name="type" class="filter-select">
                    <option value="">All Guests</option>
                    <option value="new" {{ request('type') == 'new' ? 'selected' : '' }}>New (1 booking)</option>
                    <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular (2-4 bookings)</option>
                    <option value="vip" {{ request('type') == 'vip' ? 'selected' : '' }}>VIP (5+ bookings)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <select name="sort" class="filter-select">
                    <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Recently Joined</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="bookings" {{ request('sort') == 'bookings' ? 'selected' : '' }}>Most Bookings</option>
                    <option value="revenue" {{ request('sort') == 'revenue' ? 'selected' : '' }}>Highest Revenue</option>
                </select>
            </div>
            
            <div class="filter-group" style="display: flex; align-items: end;">
                <button type="submit" class="btn-filter"> Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Guests Table -->
<div class="guests-table-wrapper">
    @if($guests->count() > 0)
        <table class="guests-table">
            <thead>
                <tr>
                    <th>Guest Information</th>
                    <th>Member Since</th>
                    <th>Total Bookings</th>
                    <th>Total Revenue</th>
                    <th>Last Booking</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guests as $guest)
                <tr>
                    <td>
                        <div class="guest-name">{{ $guest->full_name }}</div>
                        <div class="guest-email">
                            <span>📧</span>
                            <span>{{ $guest->email }}</span>
                        </div>
                        @if($guest->phone)
                            <div class="guest-phone">
                                <span>📞</span>
                                <span>{{ $guest->phone }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="guest-since">{{ $guest->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 10px; color: #d1d5db; margin-top: 2px;">
                            {{ $guest->created_at->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        <div class="bookings-count">{{ $guest->bookings_count }}</div>
                        <div class="bookings-label">{{ $guest->bookings_count == 1 ? 'Booking' : 'Bookings' }}</div>
                    </td>
                    <td>
                        <div class="revenue-value">£{{ number_format($guest->total_revenue, 2) }}</div>
                        <div class="revenue-label">Total Spent</div>
                    </td>
                    <td>
                        @if($guest->last_booking)
                            <div class="last-booking">{{ $guest->last_booking->property->name }}</div>
                            <div class="last-booking-date">
                                {{ \Carbon\Carbon::parse($guest->last_booking->check_in)->format('M d, Y') }}
                            </div>
                        @else
                            <span style="color: #9ca3af; font-size: 12px;">No bookings</span>
                        @endif
                    </td>
                    <td>
                        @if($guest->bookings_count >= 5)
                            <span class="badge badge-vip">⭐ VIP</span>
                        @elseif($guest->bookings_count >= 2)
                            <span class="badge badge-regular">👤 Regular</span>
                        @else
                            <span class="badge badge-new">✨ New</span>
                        @endif
                    </td>
                    <td>
                        <button onclick="openGuestModal({{ $guest->id }}, '{{ $guest->name }}', '{{ $guest->email }}', '{{ $guest->phone ?? '' }}', {{ $guest->bookings_count }}, {{ $guest->total_revenue ?? 0 }})" class="btn-view">
                            👁️ View History
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $guests->firstItem() }} to {{ $guests->lastItem() }} of {{ $guests->total() }} guests
            </div>
            {{ $guests->links() }}
        </div>
    @else
        <div class="no-results">
            <div class="no-results-icon">👥</div>
            <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No Guests Found</div>
            <div style="font-size: 14px; color: #9ca3af;">
                @if(request()->hasAny(['search', 'type', 'sort']))
                    Try adjusting your filters
                @else
                    No guests have registered yet
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Right Side Modal -->
<div class="side-modal-overlay" id="modalOverlay" onclick="closeGuestModal()"></div>
<div class="side-modal" id="guestModal">
    <div class="side-modal-header">
        <div class="side-modal-close" onclick="closeGuestModal()">✕</div>
        <h2 class="side-modal-title" id="modalGuestName">Guest Name</h2>
        <div class="side-modal-subtitle">
            <span id="modalGuestEmail"></span>
            <span id="modalGuestPhone"></span>
        </div>
    </div>

    <div class="side-modal-stats">
        <div class="modal-stat-card">
            <div class="modal-stat-label">Total Bookings</div>
            <div class="modal-stat-value" id="modalTotalBookings">0</div>
        </div>
        <div class="modal-stat-card stat-revenue">
            <div class="modal-stat-label">Total Revenue</div>
            <div class="modal-stat-value revenue" id="modalTotalRevenue">£0.00</div>
        </div>
    </div>

    <div class="side-modal-content">
        <div class="booking-history-header">
            <span>📚</span>
            <span>Booking History</span>
        </div>
        <div id="modalBookingsList">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <div>Loading bookings...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentGuestId = null;

function openGuestModal(guestId, name, email, phone, bookingsCount, totalRevenue) {
    currentGuestId = guestId;
    
    // Update modal header
    document.getElementById('modalGuestName').textContent = name;
    document.getElementById('modalGuestEmail').innerHTML = '📧 ' + email;
    document.getElementById('modalGuestPhone').innerHTML = phone ? '📞 ' + phone : '';
    document.getElementById('modalGuestPhone').style.display = phone ? 'inline' : 'none';
    
    // Update stats (with safety check)
    document.getElementById('modalTotalBookings').textContent = bookingsCount || 0;
    document.getElementById('modalTotalRevenue').textContent = '£' + (totalRevenue ? Number(totalRevenue).toFixed(2) : '0.00');
    
    // Show modal
    document.getElementById('modalOverlay').classList.add('active');
    document.getElementById('guestModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Load bookings
    loadGuestBookings(guestId);
}

function closeGuestModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.getElementById('guestModal').classList.remove('active');
    document.body.style.overflow = 'auto';
    currentGuestId = null;
}

function loadGuestBookings(guestId) {
    const container = document.getElementById('modalBookingsList');
    container.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <div>Loading bookings...</div>
        </div>
    `;
    
    fetch(`/admin/guests/${guestId}/bookings`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            // Extract bookings from ApiResponse format
            const bookings = data.data?.bookings || data.bookings || [];
            
            if (bookings && bookings.length > 0) {
                let html = '';
                let totalRevenue = 0;
                
                bookings.forEach(booking => {
                    // Calculate total revenue from paid orders only
                    if (booking.order && booking.order.grand_total_inc_vat && booking.order.status === 'paid') {
                        totalRevenue += parseFloat(booking.order.grand_total_inc_vat);
                    }
                    
                    const statusBadgeClass = {
                        'confirmed': 'badge-confirmed',
                        'checked_in': 'badge-checked_in',
                        'checked_out': 'badge-checked_out',
                        'cancelled': 'badge-cancelled'
                    }[booking.status] || 'badge-confirmed';
                    
                    const statusText = {
                        'confirmed': '✓ Confirmed',
                        'checked_in': '🏨 Checked In',
                        'checked_out': '✓ Completed',
                        'cancelled': '✕ Cancelled'
                    }[booking.status] || booking.status;
                    
                    const bookingTotal = booking.order && booking.order.grand_total_inc_vat ? parseFloat(booking.order.grand_total_inc_vat).toFixed(2) : '0.00';
                    
                    // Safely extract nested properties
                    const propertyName = booking.room_type && booking.room_type.property ? booking.room_type.property.name : 'N/A';
                    const roomTypeName = booking.room_type ? booking.room_type.name : 'N/A';
                    
                    // Parse dates safely
                    const checkInDate = booking.check_in ? new Date(booking.check_in).toLocaleDateString() : 'N/A';
                    const checkOutDate = booking.check_out ? new Date(booking.check_out).toLocaleDateString() : 'N/A';
                    
                    // Calculate nights from dates
                    const numNights = booking.check_in && booking.check_out ? 
                        Math.ceil((new Date(booking.check_out) - new Date(booking.check_in)) / (1000 * 60 * 60 * 24)) : 0;
                    const numGuests = booking.num_guests || 0;
                    
                    html += `
                        <div class="booking-card" onclick="window.location='/admin/booking/${booking.id}'">
                            <div class="booking-card-header">
                                <div>
                                    <div class="booking-ref-modal">${booking.booking_reference || 'N/A'}</div>
                                    <div class="booking-date-modal">${booking.created_at ? new Date(booking.created_at).toLocaleDateString() : 'N/A'}</div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge ${statusBadgeClass}">${statusText}</span>
                                    <div class="booking-amount-modal">£${bookingTotal}</div>
                                </div>
                            </div>
                            <div class="booking-card-body">
                                <div class="booking-info-row">
                                    <span class="label">Property:</span>
                                    <span class="booking-property-name">${propertyName}</span>
                                </div>
                                <div class="booking-info-row">
                                    <span class="label">Room:</span>
                                    <span>${roomTypeName}</span>
                                </div>
                                <div class="booking-info-row">
                                    <span class="label">Dates:</span>
                                    <span>${checkInDate} → ${checkOutDate}</span>
                                </div>
                                <div class="booking-info-row">
                                    <span class="label">Duration:</span>
                                    <span>${numNights} ${numNights == 1 ? 'night' : 'nights'} · ${numGuests} ${numGuests == 1 ? 'guest' : 'guests'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // Update the total revenue in the modal
                document.getElementById('modalTotalRevenue').textContent = '£' + totalRevenue.toFixed(2);
                
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="no-bookings-modal">
                        <div class="no-bookings-icon-modal">📚</div>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No Booking History</div>
                        <div style="font-size: 13px;">This guest hasn't made any bookings yet</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            container.innerHTML = `
                <div class="no-bookings-modal">
                    <div class="no-bookings-icon-modal">⚠️</div>
                    <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px; color: #dc2626;">Error Loading Bookings</div>
                    <div style="font-size: 13px;">Please try again later</div>
                </div>
            `;
        });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentGuestId) {
        closeGuestModal();
    }
});
</script>
