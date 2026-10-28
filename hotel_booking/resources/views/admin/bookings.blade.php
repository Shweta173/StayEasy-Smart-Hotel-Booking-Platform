@extends('admin.layout')

@section('title', 'Bookings & Reservations')
@section('page-title', 'Bookings & Reservations')

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

    .stat-card.stat-confirmed { border-left-color: #16a34a; }
    .stat-card.stat-checkedin { border-left-color: #0071c2; }
    .stat-card.stat-today { border-left-color: #f59e0b; }

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

    .filters-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
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

    .btn-reset {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 42px;
    }

    .btn-reset:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .bookings-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .bookings-table {
        width: 100%;
        border-collapse: collapse;
    }

    .bookings-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .bookings-table th {
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bookings-table td {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #262626;
    }

    .bookings-table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .bookings-table tbody tr:hover {
        background: #f9fafb;
    }

    .booking-ref {
        font-weight: 700;
        color: #0071c2;
        font-size: 15px;
    }

    .guest-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .guest-name {
        font-weight: 600;
        color: #262626;
    }

    .guest-email {
        font-size: 12px;
        color: #6b7280;
    }

    .guest-phone {
        font-size: 12px;
        color: #6b7280;
    }

    .date-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .date-value {
        font-weight: 600;
    }

    .date-day {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-confirmed { background: #dcfce7; color: #166534; }
    .badge-checked-in { background: #dbeafe; color: #1e40af; }
    .badge-checked-out { background: #fef3c7; color: #92400e; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .badge-pending { background: #fef3c7; color: #a16207; }

    .price-value {
        font-weight: 700;
        font-size: 16px;
        color: #166534;
    }

    .btn-view-details {
        padding: 8px 16px;
        background: #e3f2fd;
        color: #0071c2;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view-details:hover {
        background: #0071c2;
        color: white;
        transform: translateY(-1px);
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

    .no-results-text {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .no-results-subtext {
        font-size: 14px;
        color: #9ca3af;
    }

    @media (max-width: 1200px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Booking Details Modal */
    .booking-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
        padding: 20px;
    }

    .booking-modal-overlay.show {
        display: flex;
    }

    .booking-modal {
        background: white;
        border-radius: 16px;
        max-width: 1000px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 24px 32px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border-radius: 16px 16px 0 0;
    }

    .modal-title-section {
        flex: 1;
    }

    .modal-booking-ref {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .modal-booking-status {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        color: white;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .modal-body {
        padding: 32px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f5f7fa;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .modal-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .modal-section {
        background: #f9fafb;
        border-radius: 12px;
        padding: 20px;
    }

    .modal-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #003580;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-section-icon {
        font-size: 20px;
    }

    .modal-info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-info-row:last-child {
        border-bottom: none;
    }

    .modal-info-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    .modal-info-value {
        font-size: 14px;
        color: #262626;
        font-weight: 600;
        text-align: right;
    }

    .modal-section-full {
        grid-column: 1 / -1;
    }

    .financial-summary {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-top: 16px;
    }

    .financial-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 14px;
    }

    .financial-row.total {
        border-top: 2px solid #003580;
        padding-top: 16px;
        margin-top: 8px;
        font-size: 18px;
        font-weight: 700;
        color: #003580;
    }

    .modal-footer {
        padding: 20px 32px;
        border-top: 2px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
    }

    .modal-btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-btn-secondary {
        background: #f3f4f6;
        color: #4b5563;
    }

    .modal-btn-secondary:hover {
        background: #e5e7eb;
    }

    .modal-btn-primary {
        background: #0071c2;
        color: white;
    }

    .modal-btn-primary:hover {
        background: #00509e;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
    }

    .modal-btn-success {
        background: #16a34a;
        color: white;
    }

    .modal-btn-success:hover {
        background: #15803d;
    }

    .modal-btn-danger {
        background: #dc2626;
        color: white;
    }

    .modal-btn-danger:hover {
        background: #b91c1c;
    }

    .loading-spinner {
        text-align: center;
        padding: 60px 20px;
    }

    .spinner-icon {
        font-size: 48px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Pagination Styles */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-top: 2px solid #e5e7eb;
        background: #f9fafb;
    }

    .pagination-info {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .modal-sections {
            grid-template-columns: 1fr;
        }
        
        .pagination-wrapper {
            flex-direction: column;
            gap: 16px;
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card stat-checkedin">
        <div class="stat-label">Checked In</div>
        <div class="stat-value">{{ $stats['checked_in'] }}</div>
    </div>
    <div class="stat-card stat-today">
        <div class="stat-label">Today's Check-ins</div>
        <div class="stat-value">{{ $stats['today_checkins'] }}</div>
    </div>
    <div class="stat-card" style="border-left-color: #dc2626;">
        <div class="stat-label">Today's Check-Outs</div>
        <div class="stat-value">{{ $stats['today_checkouts'] }}</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="{{ route('admin.bookings') }}" id="filterForm">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    class="filter-input" 
                    placeholder="Booking ref, guest name, email, phone..."
                    value="{{ request('search') }}"
                >
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Property</label>
                <select name="property" class="filter-select">
                    <option value="">All Properties</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ request('property') == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">From Date</label>
                <input 
                    type="date" 
                    name="from_date" 
                    class="filter-input"
                    value="{{ request('from_date') }}"
                >
            </div>
            
            <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input 
                    type="date" 
                    name="to_date" 
                    class="filter-input"
                    value="{{ request('to_date') }}"
                >
            </div>
            
            <div class="filter-group" style="display: flex; align-items: end;">
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="bookings-table-wrapper">
    @if($bookings->count() > 0)
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking Ref</th>
                    <th>Guest Details</th>
                    <th>Property</th>
                    <th>Room Type</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                <tr>
                    <td>
                        <div class="booking-ref">{{ $booking->booking_reference }}</div>
                    </td>
                    <td>
                        <div class="guest-info">
                            <span class="guest-name">{{ $booking->guest->name }}</span>
                            <span class="guest-email">{{ $booking->guest->email }}</span>
                            @if($booking->guest->phone)
                                <span class="guest-phone">📞 {{ $booking->guest->phone }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <strong>{{ $booking->property->name }}</strong>
                    </td>
                    <td>
                        {{ $booking->roomType->name }}
                    </td>
                    <td>
                        <div class="date-info">
                            <span class="date-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</span>
                            <span class="date-day">{{ \Carbon\Carbon::parse($booking->check_in)->format('l') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="date-info">
                            <span class="date-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</span>
                            <span class="date-day">{{ \Carbon\Carbon::parse($booking->check_out)->format('l') }}</span>
                        </div>
                    </td>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}</strong>
                    </td>
                    <td>
                        <strong>{{ $booking->num_guests }}</strong>
                    </td>
                    <td>
                        @if($booking->status === 'confirmed')
                            <span class="badge badge-confirmed">{{ $booking->status }}</span>
                        @elseif($booking->status === 'checked_in')
                            <span class="badge badge-checked-in">Checked In</span>
                        @elseif($booking->status === 'checked_out')
                            <span class="badge badge-checked-out">Checked Out</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="badge badge-cancelled">
                                @if($booking->cancelled_by === 'guest')
                                    Cancelled by Guest
                                @elseif($booking->cancelled_by === 'admin')
                                    Cancelled by Admin
                                @else
                                    Cancelled
                                @endif
                            </span>
                        @else
                            <span class="badge badge-pending">{{ $booking->status }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="price-value">£{{ number_format($booking->order->grand_total_inc_vat ?? 0, 2) }}</div>
                    </td>
                    <td>
                        <button class="btn-view-details" onclick="event.stopPropagation(); openBookingModal({{ $booking->id }})">
                            View Details
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} bookings
            </div>
            {{ $bookings->links() }}
        </div>
    @else
        <div class="no-results">
            <div class="no-results-icon">📭</div>
            <div class="no-results-text">No Bookings Found</div>
            <div class="no-results-subtext">
                @if(request()->hasAny(['search', 'property', 'status', 'from_date', 'to_date']))
                    Try adjusting your filters
                @else
                    No bookings have been made yet
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Booking Details Modal -->
<div class="booking-modal-overlay" id="bookingModalOverlay" onclick="closeBookingModal()">
    <div class="booking-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title-section">
                <div class="modal-booking-ref" id="modalBookingRef">Loading...</div>
                <span class="modal-booking-status" id="modalBookingStatus">-</span>
            </div>
            <button class="modal-close-btn" onclick="closeBookingModal()">×</button>
        </div>
        
        <div class="modal-body" id="modalBody">
            <div class="loading-spinner">
                <div class="spinner-icon">⏳</div>
                <div style="margin-top: 12px; color: #6b7280;">Loading booking details...</div>
            </div>
        </div>
        
        <div class="modal-footer" id="modalFooter" style="display: none;">
            <!-- Action buttons will be inserted here -->
        </div>
    </div>
</div>

<script>
let currentBooking = null;

function openBookingModal(bookingId) {
    const overlay = document.getElementById('bookingModalOverlay');
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    // Show modal
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Reset modal content
    modalBody.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-icon">⏳</div>
            <div style="margin-top: 12px; color: #6b7280;">Loading booking details...</div>
        </div>
    `;
    modalFooter.style.display = 'none';
    
    // Fetch booking details
    fetch(`/admin/booking/${bookingId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(response => {
        // Extract booking from API response (nested in data property)
        const booking = response.data || response;
        currentBooking = booking;
        renderBookingDetails(booking);
    })
    .catch(error => {
        console.error('Error loading booking:', error);
        modalBody.innerHTML = `
            <div class="loading-spinner">
                <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                <div style="color: #dc2626; font-weight: 600;">Error loading booking details</div>
                <div style="color: #6b7280; margin-top: 8px;">Please try again</div>
            </div>
        `;
    });
}

function closeBookingModal() {
    const overlay = document.getElementById('bookingModalOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    currentBooking = null;
}

function renderBookingDetails(booking) {
    const modalRef = document.getElementById('modalBookingRef');
    const modalStatus = document.getElementById('modalBookingStatus');
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    // Update header
    modalRef.textContent = booking.booking_reference;
    modalStatus.textContent = booking.status_label;
    modalStatus.className = `modal-booking-status badge-${booking.status}`;
    
    // Calculate nights
    const checkIn = new Date(booking.check_in);
    const checkOut = new Date(booking.check_out);
    const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
    
    // Render booking details
    modalBody.innerHTML = `
        <div class="modal-sections">
            <!-- Booking Information -->
            <div class="modal-section">
                <div class="modal-section-title">
                    <span class="modal-section-icon">📋</span>
                    Booking Information
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Booking Reference</span>
                    <span class="modal-info-value">${booking.booking_reference}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Booking Status</span>
                    <span class="modal-info-value">${booking.status_label}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Payment Status</span>
                    <span class="modal-info-value">${booking.payment_status || 'Pending'}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Booking Date</span>
                    <span class="modal-info-value">${formatDate(booking.created_at)}</span>
                </div>
            </div>
            
            <!-- Guest Information -->
            <div class="modal-section">
                <div class="modal-section-title">
                    <span class="modal-section-icon">👤</span>
                    Guest Information
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Guest Name</span>
                    <span class="modal-info-value">${booking.guest.name}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Email</span>
                    <span class="modal-info-value">${booking.guest.email}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Phone</span>
                    <span class="modal-info-value">${booking.guest.phone || 'N/A'}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Number of Guests</span>
                    <span class="modal-info-value">${booking.num_guests}</span>
                </div>
                ${booking.special_requests ? `
                <div class="modal-info-row">
                    <span class="modal-info-label">Special Requests</span>
                    <span class="modal-info-value">${booking.special_requests}</span>
                </div>
                ` : ''}
            </div>
            
            <!-- Stay Information -->
            <div class="modal-section">
                <div class="modal-section-title">
                    <span class="modal-section-icon">🏨</span>
                    Stay Information
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Property</span>
                    <span class="modal-info-value">${booking.property.name}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Room Type</span>
                    <span class="modal-info-value">${booking.room_type.name}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Check-in Date</span>
                    <span class="modal-info-value">${formatDate(booking.check_in)}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Check-out Date</span>
                    <span class="modal-info-value">${formatDate(booking.check_out)}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Number of Nights</span>
                    <span class="modal-info-value">${nights}</span>
                </div>
            </div>
            
            <!-- Financial Information -->
            <div class="modal-section">
                <div class="modal-section-title">
                    <span class="modal-section-icon">💰</span>
                    Financial Information
                </div>
                ${booking.order ? `
                <div class="modal-info-row">
                    <span class="modal-info-label">Subtotal (Excl. VAT)</span>
                    <span class="modal-info-value">£${parseFloat(booking.order.total_exc_vat).toFixed(2)}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">VAT (20%)</span>
                    <span class="modal-info-value">£${parseFloat(booking.order.vat_amount).toFixed(2)}</span>
                </div>
                <div class="modal-info-row">
                    <span class="modal-info-label">Grand Total (Inc. VAT)</span>
                    <span class="modal-info-value" style="color: #16a34a; font-size: 18px;">£${parseFloat(booking.order.grand_total_inc_vat).toFixed(2)}</span>
                </div>
                ${booking.order.invoice ? `
                <div class="modal-info-row">
                    <span class="modal-info-label">Invoice Number</span>
                    <span class="modal-info-value">${booking.order.invoice.invoice_number}</span>
                </div>
                ` : ''}
                <div class="modal-info-row">
                    <span class="modal-info-label">Payment Method</span>
                    <span class="modal-info-value">Card</span>
                </div>
                ` : '<div style="color: #6b7280; font-style: italic;">No payment information available</div>'}
            </div>
        </div>
    `;
    
    // Render action buttons
    const buttons = [];
    
    if (booking.status === 'confirmed') {
        buttons.push(`<button class="modal-btn modal-btn-success" onclick="performCheckIn(${booking.id})">✓ Check-In</button>`);
    }
    
    if (booking.status === 'checked_in') {
        buttons.push(`<button class="modal-btn modal-btn-primary" onclick="performCheckOut(${booking.id})">🚪 Check-Out</button>`);
    }
    
    if (['confirmed', 'checked_in'].includes(booking.status)) {
        buttons.push(`<button class="modal-btn modal-btn-danger" onclick="cancelBooking(${booking.id})">✕ Cancel Booking</button>`);
    }
    
    // Show refund button only for cancelled bookings with pending refund (cancelled by guest)
    // Only visible to admin and manager roles
    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']))
    if (booking.status === 'cancelled' && booking.cancelled_by === 'guest' && booking.refund_status === 'pending' && booking.refund_amount > 0) {
        buttons.push(`<button class="modal-btn modal-btn-success" onclick="processRefundFromModal(${booking.id}, ${booking.refund_amount})">💰 Refund ($${parseFloat(booking.refund_amount).toFixed(2)})</button>`);
    }
    @endif
    
    if (booking.order && booking.order.invoice) {
        buttons.push(`<button class="modal-btn modal-btn-secondary" onclick="downloadInvoice(${booking.order.invoice.id})">📄 Download Invoice</button>`);
    }
    
    buttons.push(`<button class="modal-btn modal-btn-secondary" onclick="closeBookingModal()">Close</button>`);
    
    modalFooter.innerHTML = buttons.join('');
    modalFooter.style.display = 'flex';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    });
}

function performCheckIn(bookingId) {
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        alert('System error: Dialog library not loaded. Please refresh the page.');
        return;
    }
    
    // Blur any focused element to fix accessibility warning
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to check in this guest?",
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#0071c2',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, check in!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        },
        didOpen: () => {
            // Focus confirm button when modal opens
            const confirmButton = Swal.getConfirmButton();
            if (confirmButton) {
                setTimeout(() => confirmButton.focus(), 100);
            }
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        
        fetch(`/admin/booking/${bookingId}/check-in`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Check for success (ApiResponse uses msgtype)
            if (data.msgtype === 'success' || data.data) {
                Swal.fire({
                    title: 'Success!',
                    text: data.msg || 'Guest checked in successfully!',
                    icon: 'success',
                    iconColor: '#16a34a',
                    confirmButtonColor: '#0071c2',
                    confirmButtonText: 'OK'
                }).then(() => {
                    closeBookingModal();
                    setTimeout(() => window.location.reload(), 500);
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.msg || data.message || 'Error checking in guest',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Error checking in guest',
                icon: 'error',
                iconColor: '#dc2626',
                confirmButtonColor: '#dc2626'
            });
        });
    });
}

function performCheckOut(bookingId) {
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        alert('System error: Dialog library not loaded. Please refresh the page.');
        return;
    }
    
    // Blur any focused element to fix accessibility warning
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to check out this guest?",
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#0071c2',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, check out!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        },
        didOpen: () => {
            // Focus confirm button when modal opens
            const confirmButton = Swal.getConfirmButton();
            if (confirmButton) {
                setTimeout(() => confirmButton.focus(), 100);
            }
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        
        fetch(`/admin/booking/${bookingId}/check-out`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Check for success (ApiResponse uses msgtype)
            if (data.msgtype === 'success' || data.data) {
                Swal.fire({
                    title: 'Success!',
                    text: data.msg || 'Guest checked out successfully!',
                    icon: 'success',
                    iconColor: '#16a34a',
                    confirmButtonColor: '#0071c2',
                    confirmButtonText: 'OK'
                }).then(() => {
                    closeBookingModal();
                    setTimeout(() => window.location.reload(), 500);
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.msg || data.message || 'Error checking out guest',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Error checking out guest',
                icon: 'error',
                iconColor: '#dc2626',
                confirmButtonColor: '#dc2626'
            });
        });
    });
}

function cancelBooking(bookingId) {
    console.log('Cancel booking called with ID:', bookingId);
    
    showConfirmModal(
        'Cancel Booking',
        'Are you sure you want to cancel this booking? This action cannot be undone.',
        'Cancel Booking',
        'danger',
        function() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            const inputTag = document.querySelector('input[name="_token"]');
            const csrfToken = (metaTag && metaTag.getAttribute('content')) 
                           || (inputTag && inputTag.value)
                           || '{{ csrf_token() }}';
            
            const requestData = {
                status: 'cancelled',
                notes: 'Cancelled by admin'
            };
            
            console.log('Sending cancel request:', requestData);
            console.log('Using CSRF token:', csrfToken);
            
            fetch('/admin/booking/' + bookingId + '/cancel', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(function(response) {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(function(data) {
                console.log('Response data:', data);
                // Check for success in multiple formats
                if (data.success || data.msgtype === 'success') {
                    showToast('Booking cancelled successfully', 'success');
                    closeBookingModal();
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    const errorMsg = data.message || data.msg || data.error || 'Error cancelling booking';
                    console.error('Cancellation failed:', errorMsg, data);
                    showToast(errorMsg, 'error');
                }
            })
            .catch(function(error) {
                console.error('Catch block - Error cancelling:', error);
                showToast('Network error: ' + error.message, 'error');
            });
        }
    );
}

function processRefundFromModal(bookingId, refundAmount) {
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        alert('System error: Dialog library not loaded. Please refresh the page.');
        return;
    }
    
    // Blur any focused element
    if (document.activeElement) {
        document.activeElement.blur();
    }
    
    Swal.fire({
        title: 'Process Refund?',
        html: `Are you sure you want to process a refund of <strong>$${refundAmount.toFixed(2)}</strong>?<br><br><small style="color: #6b7280;">This will mark the refund as completed in the system.</small>`,
        icon: 'question',
        iconColor: '#0071c2',
        showCancelButton: true,
        confirmButtonColor: '#0071c2',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, Process Refund',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            confirmButton: 'swal-custom-confirm',
            cancelButton: 'swal-custom-cancel'
        },
        didOpen: () => {
            const confirmButton = Swal.getConfirmButton();
            if (confirmButton) {
                confirmButton.focus();
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/booking/${bookingId}/refund`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Refund Processed!',
                        text: 'The refund has been marked as completed.',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        customClass: {
                            popup: 'swal-custom-popup',
                            title: 'swal-custom-title',
                            confirmButton: 'swal-custom-confirm'
                        }
                    }).then(() => {
                        closeBookingModal();
                        setTimeout(() => window.location.reload(), 1000);
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to process refund',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#0071c2',
                        customClass: {
                            popup: 'swal-custom-popup',
                            title: 'swal-custom-title',
                            confirmButton: 'swal-custom-confirm'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to process refund',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#0071c2',
                    customClass: {
                        popup: 'swal-custom-popup',
                        title: 'swal-custom-title',
                        confirmButton: 'swal-custom-confirm'
                    }
                });
            });
        }
    });
}

// ── Confirm Modal ─────────────────────────────────────────────────────────
function showConfirmModal(title, message, confirmLabel, type, onConfirm) {
    // Remove any existing confirm modal
    const existing = document.getElementById('siteConfirmModal');
    if (existing) existing.remove();

    const color = type === 'danger' ? '#dc2626' : '#0071c2';

    const modal = document.createElement('div');
    modal.id = 'siteConfirmModal';
    modal.style.cssText = `
        position:fixed;top:0;left:0;right:0;bottom:0;
        background:rgba(0,0,0,.5);z-index:9999;
        display:flex;align-items:center;justify-content:center;
        animation:fadeInBg .2s ease;
    `;
    modal.innerHTML = `
        <div style="
            background:#fff;border-radius:12px;padding:32px;
            max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.25);
            animation:slideUpModal .25s ease;
        ">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="
                    width:40px;height:40px;border-radius:50%;
                    background:${type === 'danger' ? '#fee2e2' : '#dbeafe'};
                    display:flex;align-items:center;justify-content:center;
                    font-size:20px;flex-shrink:0;
                ">${type === 'danger' ? '⚠️' : 'ℹ️'}</div>
                <h3 style="margin:0;font-size:18px;font-weight:700;color:#111;">${title}</h3>
            </div>
            <p style="margin:0 0 24px;font-size:14px;color:#4b5563;line-height:1.6;">${message}</p>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button id="confirmModalCancel" style="
                    padding:10px 20px;border:1px solid #d1d5db;border-radius:7px;
                    background:#f9fafb;color:#374151;font-size:14px;font-weight:600;
                    cursor:pointer;transition:background .15s;
                ">Keep Booking</button>
                <button id="confirmModalOk" style="
                    padding:10px 20px;border:none;border-radius:7px;
                    background:${color};color:#fff;font-size:14px;font-weight:600;
                    cursor:pointer;transition:opacity .15s;
                ">${confirmLabel}</button>
            </div>
        </div>
        <style>
            @keyframes fadeInBg { from{opacity:0} to{opacity:1} }
            @keyframes slideUpModal { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        </style>
    `;

    document.body.appendChild(modal);

    document.getElementById('confirmModalCancel').onclick = () => modal.remove();
    document.getElementById('confirmModalOk').onclick = () => {
        modal.remove();
        onConfirm();
    };
    // Click outside to dismiss
    modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
}

function downloadInvoice(invoiceId) {
    window.open(`/admin/invoices/${invoiceId}/pdf`, '_blank');
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeBookingModal();
    }
});
</script>
@endsection
