@extends('admin.layout')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@section('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0071c2;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 24px;
        transition: all 0.2s ease;
    }

    .back-link:hover {
        color: #00509e;
        gap: 12px;
    }

    .booking-header {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.2);
    }

    .booking-header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .booking-ref-large {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .booking-status-large {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-confirmed { background: #16a34a; }
    .status-checked-in { background: #0071c2; }
    .status-checked-out { background: #f59e0b; }
    .status-cancelled { background: #dc2626; }
    .status-pending { background: #ca8a04; }

    .booking-date-info {
        text-align: right;
    }

    .booking-created {
        font-size: 13px;
        opacity: 0.9;
        margin-bottom: 4px;
    }

    .booking-actions {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: 2px solid white;
        background: transparent;
        color: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        background: white;
        color: #003580;
    }

    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-icon {
        font-size: 24px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
    }

    .detail-value {
        font-size: 14px;
        color: #262626;
        font-weight: 600;
        text-align: right;
    }

    .guest-info-block {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .guest-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .guest-row-icon {
        width: 40px;
        height: 40px;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .guest-row-info {
        flex: 1;
    }

    .guest-row-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .guest-row-value {
        font-size: 15px;
        color: #262626;
        font-weight: 600;
        margin-top: 2px;
    }

    .price-breakdown {
        background: #f9fafb;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 14px;
    }

    .price-row-label {
        color: #6b7280;
    }

    .price-row-value {
        font-weight: 600;
        color: #262626;
    }

    .price-row.total {
        border-top: 2px solid #e5e7eb;
        padding-top: 16px;
        margin-top: 8px;
    }

    .price-row.total .price-row-label {
        font-size: 16px;
        font-weight: 700;
        color: #262626;
    }

    .price-row.total .price-row-value {
        font-size: 20px;
        font-weight: 700;
        color: #16a34a;
    }

    .stay-timeline {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }

    .timeline-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        margin-top: 32px;
    }

    .timeline-line {
        position: absolute;
        top: 40px;
        left: 80px;
        right: 80px;
        height: 4px;
        background: #e5e7eb;
        z-index: 0;
    }

    .timeline-progress {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: linear-gradient(90deg, #0071c2, #16a34a);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .timeline-point {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 1;
        position: relative;
    }

    .timeline-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: white;
        border: 4px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .timeline-circle.active {
        border-color: #0071c2;
        box-shadow: 0 0 0 4px rgba(0, 113, 194, 0.1);
    }

    .timeline-circle.completed {
        border-color: #16a34a;
        background: #dcfce7;
    }

    .timeline-label {
        font-size: 13px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .timeline-date {
        font-size: 15px;
        font-weight: 600;
        color: #262626;
        margin-top: 4px;
    }

    .timeline-nights {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        color: #0071c2;
        border: 2px solid #0071c2;
        z-index: 2;
    }

    /* Custom SweetAlert2 Styling */
    .swal-custom-popup {
        border-radius: 16px !important;
        padding: 40px 30px 30px !important;
    }

    .swal-custom-title {
        font-size: 28px !important;
        font-weight: 700 !important;
        color: #262626 !important;
    }

    .swal-custom-confirm {
        padding: 14px 32px !important;
        border-radius: 8px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3) !important;
    }

    .swal-custom-cancel {
        padding: 14px 32px !important;
        border-radius: 8px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
    }

    .swal2-icon.swal2-warning {
        border-color: #f59e0b !important;
        color: #f59e0b !important;
    }

    /* Fix z-index - SweetAlert2 must be on top of everything including modals */
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 1000000 !important;
    }
    
    /* Ensure backdrop is also above modal */
    body.swal2-shown > [aria-hidden="true"] {
        filter: blur(2px);
    }

    @media (max-width: 1024px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
        
        .timeline-container {
            flex-direction: column;
            gap: 32px;
        }
        
        .timeline-line {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<a href="{{ route('admin.bookings') }}" class="back-link">
    ← Back to Bookings
</a>

<!-- Booking Header -->
<div class="booking-header">
    <div class="booking-header-content">
        <div>
            <div class="booking-ref-large">{{ $booking->booking_reference }}</div>
            <span class="booking-status-large status-{{ str_replace('_', '-', $booking->status) }}">
                {{ ucwords(str_replace('_', ' ', $booking->status)) }}
            </span>
            <div class="booking-actions">
                @if($booking->status === 'confirmed')
                    <button class="btn-action" onclick="checkInBooking({{ $booking->id }})">✓ Check In</button>
                    <button class="btn-action" onclick="modifyBooking({{ $booking->id }})">✏️ Modify</button>
                    <button class="btn-action" onclick="cancelBooking({{ $booking->id }})">❌ Cancel</button>
                @elseif($booking->status === 'checked_in')
                    <button class="btn-action" onclick="checkOutBooking({{ $booking->id }})">→ Check Out</button>
                @endif
                <button class="btn-action" onclick="emailGuest('{{ $booking->guest->email }}', '{{ $booking->guest->name }}')">📧 Email Guest</button>
                <button class="btn-action" onclick="printBooking()">🖨️ Print</button>
            </div>
        </div>
        <div class="booking-date-info">
            <div class="booking-created">Created on {{ $booking->created_at->format('M d, Y \a\t g:i A') }}</div>
            <div class="booking-created">
                @if($booking->order)
                    Payment: <strong>£{{ number_format($booking->order->grand_total_inc_vat, 2) }}</strong> 
                    ({{ ucfirst($booking->order->status) }})
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stay Timeline -->
<div class="stay-timeline">
    <h2 class="card-title">
        <span class="card-icon">📅</span>
        Stay Timeline
    </h2>
    <div class="timeline-container">
        <div class="timeline-line">
            <div class="timeline-progress" style="width: {{ $booking->status === 'checked_in' ? '50%' : ($booking->status === 'checked_out' ? '100%' : '0%') }}"></div>
            <div class="timeline-nights">
                {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }} Nights
            </div>
        </div>
        
        <div class="timeline-point">
            <div class="timeline-circle {{ in_array($booking->status, ['confirmed', 'checked_in', 'checked_out']) ? 'completed' : '' }}">
                🏨
            </div>
            <div class="timeline-label">Booking Created</div>
            <div class="timeline-date">{{ $booking->created_at->format('M d, Y') }}</div>
        </div>
        
        <div class="timeline-point">
            <div class="timeline-circle {{ in_array($booking->status, ['checked_in', 'checked_out']) ? 'completed' : ($booking->status === 'confirmed' ? 'active' : '') }}">
                🔑
            </div>
            <div class="timeline-label">Check-in</div>
            <div class="timeline-date">{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</div>
        </div>
        
        <div class="timeline-point">
            <div class="timeline-circle {{ $booking->status === 'checked_out' ? 'completed' : ($booking->status === 'checked_in' ? 'active' : '') }}">
                👋
            </div>
            <div class="timeline-label">Check-out</div>
            <div class="timeline-date">{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</div>
        </div>
    </div>
</div>

<!-- Details Grid -->
<div class="details-grid">
    <!-- Guest Information -->
    <div class="detail-card">
        <h2 class="card-title">
            <span class="card-icon">👤</span>
            Guest Information
        </h2>
        <div class="guest-info-block">
            <div class="guest-row">
                <div class="guest-row-icon">👤</div>
                <div class="guest-row-info">
                    <div class="guest-row-label">Full Name</div>
                    <div class="guest-row-value">{{ $booking->guest->name }}</div>
                </div>
            </div>
            <div class="guest-row">
                <div class="guest-row-icon">📧</div>
                <div class="guest-row-info">
                    <div class="guest-row-label">Email Address</div>
                    <div class="guest-row-value">{{ $booking->guest->email }}</div>
                </div>
            </div>
            @if($booking->guest->phone)
            <div class="guest-row">
                <div class="guest-row-icon">📞</div>
                <div class="guest-row-info">
                    <div class="guest-row-label">Phone Number</div>
                    <div class="guest-row-value">{{ $booking->guest->phone }}</div>
                </div>
            </div>
            @endif
            <div class="guest-row">
                <div class="guest-row-icon">👥</div>
                <div class="guest-row-info">
                    <div class="guest-row-label">Number of Guests</div>
                    <div class="guest-row-value">{{ $booking->num_guests }} {{ $booking->num_guests == 1 ? 'Guest' : 'Guests' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property & Room Details -->
    <div class="detail-card">
        <h2 class="card-title">
            <span class="card-icon">🏨</span>
            Property & Room Details
        </h2>
        <div class="detail-row">
            <span class="detail-label">Property</span>
            <span class="detail-value">{{ $booking->property->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Location</span>
            <span class="detail-value">{{ $booking->property->location }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Room Type</span>
            <span class="detail-value">{{ $booking->roomType->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Max Occupancy</span>
            <span class="detail-value">{{ $booking->roomType->max_occupancy }} Guests</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Base Price</span>
            <span class="detail-value">${{ number_format($booking->roomType->base_price, 2) }}/night</span>
        </div>
    </div>

    <!-- Stay Details -->
    <div class="detail-card">
        <h2 class="card-title">
            <span class="card-icon">📆</span>
            Stay Details
        </h2>
        <div class="detail-row">
            <span class="detail-label">Check-in Date</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('l, M d, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-in Time</span>
            <span class="detail-value">2:00 PM</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-out Date</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('l, M d, Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-out Time</span>
            <span class="detail-value">11:00 AM</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Total Nights</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }} Nights</span>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="detail-card">
        <h2 class="card-title">
            <span class="card-icon">💳</span>
            Payment Details
        </h2>
        @if($booking->order)
        <div class="detail-row">
            <span class="detail-label">Order ID</span>
            <span class="detail-value">{{ $booking->order->order_number }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Method</span>
            <span class="detail-value">{{ ucfirst($booking->order->payment_method) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Status</span>
            <span class="detail-value">{{ ucfirst($booking->order->status) }}</span>
        </div>
        @if($booking->order->stripe_payment_intent_id)
        <div class="detail-row">
            <span class="detail-label">Stripe Payment ID</span>
            <span class="detail-value" style="font-size: 11px;">{{ $booking->order->stripe_payment_intent_id }}</span>
        </div>
        @endif
        <div class="price-breakdown">
            <div class="price-row">
                <span class="price-row-label">Subtotal</span>
                <span class="price-row-value">${{ number_format($booking->order->subtotal_ex_vat, 2) }}</span>
            </div>
            @if(!$settings->prices_include_vat)
            <div class="price-row">
                <span class="price-row-label">VAT ({{ $booking->order->tax_rate }}%)</span>
                <span class="price-row-value">${{ number_format($booking->order->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="price-row total">
                <span class="price-row-label">Total Paid @if($settings->prices_include_vat)(incl. VAT)@endif</span>
                <span class="price-row-value">£{{ number_format($booking->order->grand_total_inc_vat, 2) }}</span>
            </div>
        </div>
        @else
        <div style="text-align: center; color: #6b7280; padding: 20px;">
            No payment information available
        </div>
        @endif
    </div>

    <!-- Cancellation & Refund Details -->
    @if($booking->status === 'cancelled' && $booking->cancelled_at)
    <div class="detail-card" style="border-left: 4px solid #dc2626;">
        <h2 class="card-title">
            <span class="card-icon">🚫</span>
            Cancellation & Refund Information
        </h2>
        <div class="detail-row">
            <span class="detail-label">Cancelled By</span>
            <span class="detail-value" style="color: #dc2626; font-weight: 700;">Guest</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Cancelled On</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($booking->cancelled_at)->format('l, M d, Y \a\t h:i A') }}</span>
        </div>
        @if($booking->cancellation_reason)
        <div class="detail-row">
            <span class="detail-label">Cancellation Reason</span>
            <span class="detail-value">{{ $booking->cancellation_reason }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Refund Status</span>
            <span class="detail-value">
                @if($booking->refund_status === 'pending')
                    <span style="color: #ca8a04; font-weight: 700;">⏳ Pending</span>
                @elseif($booking->refund_status === 'processing')
                    <span style="color: #0071c2; font-weight: 700;">🔄 Processing</span>
                @elseif($booking->refund_status === 'completed')
                    <span style="color: #16a34a; font-weight: 700;">✅ Completed</span>
                @elseif($booking->refund_status === 'failed')
                    <span style="color: #dc2626; font-weight: 700;">❌ Failed</span>
                @else
                    <span style="color: #6b7280; font-weight: 700;">N/A</span>
                @endif
            </span>
        </div>
        <div class="price-breakdown" style="background: #fef2f2; border-left: 4px solid #dc2626;">
            <div class="price-row">
                <span class="price-row-label">Original Amount</span>
                <span class="price-row-value">${{ number_format($booking->order->grand_total_inc_vat ?? 0, 2) }}</span>
            </div>
            <div class="price-row">
                <span class="price-row-label">Refund Percentage</span>
                <span class="price-row-value">{{ $booking->refund_percentage }}%</span>
            </div>
            <div class="price-row total">
                <span class="price-row-label">Refund Amount</span>
                <span class="price-row-value" style="color: #dc2626;">${{ number_format($booking->refund_amount, 2) }}</span>
            </div>
        </div>
        @if($booking->refund_processed_at)
        <div class="detail-row" style="margin-top: 12px;">
            <span class="detail-label">Refund Processed On</span>
            <span class="detail-value">{{ \Carbon\Carbon::parse($booking->refund_processed_at)->format('l, M d, Y \a\t h:i A') }}</span>
        </div>
        @endif
        
        <!-- Refund Action Button -->
        @if($booking->refund_status === 'pending' && $booking->refund_amount > 0)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #fee2e2;">
            <button 
                onclick="processRefund({{ $booking->id }}, {{ $booking->refund_amount }})"
                class="btn-action" 
                style="background: #0071c2; color: white; border: none; width: 100%; padding: 14px; font-size: 16px;">
                💰 Process Refund (${{ number_format($booking->refund_amount, 2) }})
            </button>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function processRefund(bookingId, refundAmount) {
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
        buttonsStyling: true,
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/booking/${bookingId}/refund`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
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
                        window.location.reload();
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

function checkInBooking(bookingId) {
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
        buttonsStyling: true,
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/booking/${bookingId}/check-in`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Guest checked in successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to check in guest',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while checking in',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function checkOutBooking(bookingId) {
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
        buttonsStyling: true,
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/booking/${bookingId}/check-out`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Guest checked out successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to check out guest',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while checking out',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function cancelBooking(bookingId) {
    Swal.fire({
        title: 'Cancel Booking',
        text: 'Enter cancellation reason:',
        input: 'textarea',
        inputPlaceholder: 'Enter reason for cancellation...',
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel booking!',
        cancelButtonText: 'No, keep it',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to enter a cancellation reason!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/booking/${bookingId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Cancelled!',
                        text: 'Booking cancelled successfully!',
                        icon: 'success',
                        iconColor: '#16a34a',
                        confirmButtonColor: '#0071c2',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to cancel booking',
                        icon: 'error',
                        iconColor: '#dc2626',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred',
                    icon: 'error',
                    iconColor: '#dc2626',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

function modifyBooking(bookingId) {
    alert('Modify booking feature coming soon!\n\nThis will allow you to:\n- Change dates\n- Change room type\n- Update guest count\n- Modify special requests');
}

function emailGuest(email, name) {
    const subject = encodeURIComponent(`Booking Confirmation - {{ $booking->booking_reference }}`);
    const body = encodeURIComponent(`Dear ${name},\n\nThank you for your booking.\n\nBooking Reference: {{ $booking->booking_reference }}\nProperty: {{ $booking->property->name }}\nCheck-in: {{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}\nCheck-out: {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}\n\nWe look forward to welcoming you!\n\nBest regards,\n{{ $booking->property->name }} Team`);
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
}

function printBooking() {
    window.print();
}
</script>
@endsection
