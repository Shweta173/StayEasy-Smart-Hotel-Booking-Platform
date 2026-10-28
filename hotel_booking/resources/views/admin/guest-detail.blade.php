@extends('admin.layout')

@section('title', 'Guest Details - ' . $guest->full_name)
@section('page-title', 'Guest Details')

@section('styles')
<style>
    .guest-header {
        background: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .guest-info {
        flex: 1;
    }

    .guest-name-title {
        font-size: 32px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 12px;
    }

    .guest-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 12px;
    }

    .guest-badge-vip {
        background: #fef3c7;
        color: #a16207;
    }

    .guest-badge-regular {
        background: #dbeafe;
        color: #1e40af;
    }

    .guest-badge-new {
        background: #d1fae5;
        color: #065f46;
    }

    .guest-meta {
        display: flex;
        gap: 24px;
        margin-top: 16px;
    }

    .guest-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #6b7280;
    }

    .guest-meta-icon {
        font-size: 16px;
    }

    .guest-actions {
        display: flex;
        gap: 12px;
        flex-direction: column;
    }

    .btn-action {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #0071c2;
    }

    .stat-card.stat-bookings { border-left-color: #16a34a; }
    .stat-card.stat-revenue { border-left-color: #f59e0b; }
    .stat-card.stat-avg { border-left-color: #8b5cf6; }

    .stat-label {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #262626;
    }

    .stat-value.revenue {
        color: #16a34a;
    }

    .section-header {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
    }

    .booking-history-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .booking-item {
        padding: 24px;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .booking-item:last-child {
        border-bottom: none;
    }

    .booking-item:hover {
        background: #f9fafb;
    }

    .booking-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }

    .booking-ref {
        font-size: 16px;
        font-weight: 700;
        color: #0071c2;
        margin-bottom: 6px;
    }

    .booking-date {
        font-size: 12px;
        color: #9ca3af;
    }

    .booking-status {
        text-align: right;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }

    .badge-confirmed { background: #dcfce7; color: #166534; }
    .badge-checked_in { background: #dbeafe; color: #1e40af; }
    .badge-checked_out { background: #f3f4f6; color: #6b7280; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }

    .booking-amount {
        font-size: 18px;
        font-weight: 700;
        color: #16a34a;
        font-family: 'Courier New', monospace;
    }

    .booking-details {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .booking-detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .booking-detail-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .booking-detail-value {
        font-size: 14px;
        color: #262626;
        font-weight: 600;
    }

    .booking-property {
        font-weight: 700;
        color: #0071c2;
    }

    .no-bookings {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .no-bookings-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .booking-details {
            grid-template-columns: 1fr;
        }

        .guest-header {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>
@endsection

@section('content')
<!-- Guest Header -->
<div class="guest-header">
    <div class="guest-info">
        <h1 class="guest-name-title">
            {{ $guest->full_name }}
            @if($bookings_count >= 5)
                <span class="guest-badge guest-badge-vip">⭐ VIP Guest</span>
            @elseif($bookings_count >= 2)
                <span class="guest-badge guest-badge-regular">👤 Regular Guest</span>
            @else
                <span class="guest-badge guest-badge-new">✨ New Guest</span>
            @endif
        </h1>
        <div class="guest-meta">
            <div class="guest-meta-item">
                <span class="guest-meta-icon">📧</span>
                <span>{{ $guest->email }}</span>
            </div>
            @if($guest->phone)
            <div class="guest-meta-item">
                <span class="guest-meta-icon">📞</span>
                <span>{{ $guest->phone }}</span>
            </div>
            @endif
            <div class="guest-meta-item">
                <span class="guest-meta-icon">📅</span>
                <span>Member since {{ $guest->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    </div>
    <div class="guest-actions">
        <a href="{{ route('admin.guests') }}" class="btn-action btn-secondary">
            ← Back to Guests
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value">{{ $bookings_count }}</div>
    </div>
    <div class="stat-card stat-revenue">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value revenue">£{{ number_format($total_revenue, 2) }}</div>
    </div>
    <div class="stat-card stat-avg">
        <div class="stat-label">Avg Booking Value</div>
        <div class="stat-value revenue">£{{ number_format($bookings_count > 0 ? $total_revenue / $bookings_count : 0, 2) }}</div>
    </div>
    <div class="stat-card stat-bookings">
        <div class="stat-label">Completed Stays</div>
        <div class="stat-value">{{ $completed_bookings }}</div>
    </div>
</div>

<!-- Booking History Section -->
<div class="section-header">
    <h2 class="section-title">📚 Booking History ({{ $bookings_count }})</h2>
</div>

<div class="booking-history-wrapper">
    @if($bookings->count() > 0)
        @foreach($bookings as $booking)
        <div class="booking-item" onclick="window.location='{{ route('admin.booking-detail', $booking->id) }}'">
            <div class="booking-item-header">
                <div>
                    <div class="booking-ref">{{ $booking->booking_reference }}</div>
                    <div class="booking-date">Booked on {{ $booking->created_at->format('M d, Y · g:i A') }}</div>
                </div>
                <div class="booking-status">
                    @if($booking->status === 'confirmed')
                        <span class="badge badge-confirmed">✓ Confirmed</span>
                    @elseif($booking->status === 'checked_in')
                        <span class="badge badge-checked_in">🏨 Checked In</span>
                    @elseif($booking->status === 'checked_out')
                        <span class="badge badge-checked_out">✓ Completed</span>
                    @else
                        <span class="badge badge-cancelled">✕ Cancelled</span>
                    @endif
                    <div class="booking-amount">£{{ number_format($booking->order->grand_total_inc_vat ?? 0, 2) }}</div>
                </div>
            </div>
            
            <div class="booking-details">
                <div class="booking-detail-item">
                    <div class="booking-detail-label">Property & Room</div>
                    <div class="booking-detail-value booking-property">{{ $booking->property->name }}</div>
                    <div style="font-size: 12px; color: #6b7280;">{{ $booking->roomType->name }}</div>
                </div>
                <div class="booking-detail-item">
                    <div class="booking-detail-label">Check-in / Check-out</div>
                    <div class="booking-detail-value">
                        {{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}
                    </div>
                </div>
                <div class="booking-detail-item">
                    <div class="booking-detail-label">Duration & Guests</div>
                    <div class="booking-detail-value">
                        {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }} nights
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        {{ $booking->num_guests }} {{ $booking->num_guests == 1 ? 'guest' : 'guests' }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="no-bookings">
            <div class="no-bookings-icon">📚</div>
            <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No Booking History</div>
            <div style="font-size: 14px; color: #9ca3af;">
                This guest hasn't made any bookings yet
            </div>
        </div>
    @endif
</div>
@endsection
