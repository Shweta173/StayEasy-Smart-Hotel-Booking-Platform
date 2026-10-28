@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    .welcome-text {
        font-size: 16px;
        color: #6b6b6b;
        margin-bottom: 32px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }

    .stat-icon-blue { background: #e3f2fd; }
    .stat-icon-green { background: #e8f5e9; }
    .stat-icon-purple { background: #f3e5f5; }
    .stat-icon-orange { background: #fff3e0; }
    .stat-icon-red { background: #ffebee; }

    .stat-label {
        font-size: 14px;
        color: #6b6b6b;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 8px;
    }

    .section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 32px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e0e0e0;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
    }

    .btn-secondary {
        background: #0071c2;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        background: #00509e;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead tr {
        background: #f9fafb;
    }

    .table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        color: #6b6b6b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 16px;
        border-top: 1px solid #e0e0e0;
        font-size: 14px;
    }

    .table tbody tr:hover {
        background: #f9fafb;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-success { background: #dcfce7; color: #166534; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }

    .text-muted {
        color: #6b6b6b;
    }

    .no-data {
        text-align: center;
        padding: 48px;
        color: #6b6b6b;
        font-size: 16px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .action-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #262626;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .action-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .action-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .action-desc {
        font-size: 13px;
        color: #6b6b6b;
    }
</style>
@endsection

@section('content')
    <p class="welcome-text">Welcome back, {{ session('admin_user')['name'] ?? session('admin_name') ?? 'Admin' }}! Here's what's happening today.</p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value">{{ $stats['total_bookings'] }}</div>
        </div>

        @php
            $userRole = session('admin_user')['role'] ?? session('admin_role') ?? 'front_desk';
            $isFrontDesk = $userRole === 'front_desk';
        @endphp

        @if(!$isFrontDesk)
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">${{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
        @endif

        <div class="stat-card">
            <div class="stat-label">Today's Check-ins</div>
            <div class="stat-value">{{ $stats['todays_checkins'] }}</div>
        </div>
    </div>

    <div class="section" style="margin-bottom: 32px;">
        <h2 class="section-title" style="margin-bottom: 20px;">Quick Actions</h2>
        <div class="quick-actions">
            <a href="{{ route('admin.bookings') }}" class="action-card">
              
                <div class="action-title">View All Bookings</div>
                <div class="action-desc">Manage reservations and check-ins</div>
            </a>
            <a href="#" class="action-card">
                
                <div class="action-title">Today's Activity</div>
                <div class="action-desc">Arrivals and departures</div>
            </a>
            <a href="{{ route('admin.properties') }}" class="action-card">
                <div class="action-icon"></div>
                <div class="action-title">Manage Properties</div>
                <div class="action-desc">Add or edit hotel properties</div>
            </a>
            <a href="#" class="action-card">
                <div class="action-icon"></div>
                <div class="action-title">View Orders</div>
                <div class="action-desc">Track payments and invoices</div>
            </a>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Recent Bookings</h2>
            <a href="{{ route('admin.bookings') }}" class="btn-secondary">View All</a>
        </div>

        @if($recentBookings->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Guest</th>
                        <th>Property</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->booking_reference }}</strong></td>
                        <td>{{ $booking->guest->name }}</td>
                        <td>{{ $booking->property->name }}</td>
                        <td>{{ $booking->roomType->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</td>
                        <td>
                            @if($booking->status === 'confirmed')
                                <span class="badge badge-success">{{ $booking->status }}</span>
                            @elseif($booking->status === 'checked_in')
                                <span class="badge badge-info">{{ $booking->status }}</span>
                            @elseif($booking->status === 'checked_out')
                                <span class="badge badge-warning">{{ $booking->status }}</span>
                            @else
                                <span class="badge badge-danger">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $booking->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                📭 No bookings yet
            </div>
        @endif
    </div>
@endsection
