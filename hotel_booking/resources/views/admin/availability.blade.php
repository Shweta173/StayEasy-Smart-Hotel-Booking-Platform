@extends('admin.layout')

@section('title', 'Availability Calendar')
@section('page-title', 'Availability Calendar')

@section('styles')
<style>
    .calendar-wrapper {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .calendar-controls {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .property-selector {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        min-width: 200px;
        background: white;
    }

    .month-navigation {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .nav-btn:hover {
        background: #f3f4f6;
        border-color: #0071c2;
    }

    .current-month {
        font-size: 16px;
        font-weight: 600;
        color: #262626;
        min-width: 150px;
        text-align: center;
    }

    .legend {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #6b6b6b;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        border: 1px solid #e0e0e0;
    }

    .legend-color.available { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
    .legend-color.booked { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
    .legend-color.blocked { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); }

    /* Property Sections */
    .properties-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .property-section {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    }

    .property-header {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .property-title {
        font-size: 16px;
        font-weight: 700;
    }

    .property-info {
        font-size: 11px;
        opacity: 0.9;
        margin-top: 2px;
    }

    /* Availability Table */
    .availability-table-wrapper {
        overflow-x: auto;
        background: white;
    }

    .availability-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .availability-table thead th {
        background: #f8f9fa;
        padding: 8px 4px;
        text-align: center;
        font-weight: 600;
        font-size: 10px;
        color: #374151;
        border-bottom: 2px solid #e0e0e0;
        border-right: 1px solid #e0e0e0;
        position: sticky;
        top: 0;
        z-index: 10;
        min-width: 60px;
    }

    .availability-table thead th:first-child {
        text-align: left;
        padding-left: 12px;
        min-width: 160px;
        position: sticky;
        left: 0;
        z-index: 11;
        background: #f8f9fa;
    }

    .availability-table tbody th {
        background: #ffffff;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #262626;
        border-right: 2px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        position: sticky;
        left: 0;
        z-index: 5;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    .room-type-name-cell {
        font-weight: 700;
        color: #262626;
        font-size: 13px;
    }

    .room-type-units {
        font-size: 10px;
        color: #6b7280;
        font-weight: 500;
        margin-top: 2px;
    }

    .availability-table td {
        padding: 0;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 60px;
        height: 45px;
        vertical-align: middle;
        position: relative;
    }

    .availability-table td:hover {
        background: #f9fafb;
        box-shadow: inset 0 0 0 2px #0071c2;
        z-index: 8;
    }

    .date-cell {
        position: relative;
    }

    .date-cell.today {
        background: #fffbeb;
        box-shadow: inset 0 0 0 2px #fbbf24;
    }

    .date-cell.today::after {
        content: 'TODAY';
        position: absolute;
        top: 1px;
        right: 1px;
        font-size: 7px;
        font-weight: 700;
        color: #f59e0b;
        background: #fef3c7;
        padding: 1px 3px;
        border-radius: 2px;
        letter-spacing: 0.3px;
    }

    .availability-status {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 4px 2px;
        position: relative;
    }

    .availability-number {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .availability-label {
        font-size: 8px;
        font-weight: 700;
        text-transform: lowercase;
        letter-spacing: 0.2px;
    }

    .hold-indicator {
        position: absolute;
        top: 1px;
        right: 1px;
        background: #f97316;
        color: white;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 7px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    /* Status Colors */
    .status-available {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    }

    .status-available .availability-number {
        color: #15803d;
    }

    .status-available .availability-label {
        color: #16a34a;
    }

    .status-partial {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }

    .status-partial .availability-number {
        color: #a16207;
    }

    .status-partial .availability-label {
        color: #ca8a04;
    }

    .status-hold {
        background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        position: relative;
    }

    .status-hold::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 20px 20px 0;
        border-color: transparent #f97316 transparent transparent;
    }

    .status-hold .availability-number {
        color: #c2410c;
    }

    .status-hold .availability-label {
        color: #ea580c;
    }

    .status-booked {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }

    .status-booked .availability-number {
        color: #991b1b;
    }

    .status-booked .availability-label {
        color: #dc2626;
    }

    .status-blocked {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        cursor: not-allowed;
    }

    .status-blocked .availability-number {
        color: #475569;
        font-size: 20px;
    }

    .status-blocked .availability-label {
        color: #64748b;
    }

    .date-header-main {
        font-size: 11px;
        font-weight: 700;
        color: #262626;
        display: block;
        margin-bottom: 1px;
    }

    .date-header-day {
        font-size: 9px;
        color: #6b7280;
        font-weight: 600;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Improved Status Colors - More Vibrant */
    .status-available {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    }

    .status-available .availability-number {
        color: #15803d;
    }

    .status-available .availability-label {
        color: #16a34a;
    }

    .status-partial {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }

    .status-partial .availability-number {
        color: #a16207;
    }

    .status-partial .availability-label {
        color: #ca8a04;
    }

    .status-booked {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }

    .status-booked .availability-number {
        color: #991b1b;
    }

    .status-booked .availability-label {
        color: #dc2626;
    }

    .status-blocked {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        cursor: not-allowed;
    }

    .status-blocked .availability-number {
        color: #475569;
    }

    .status-blocked .availability-label {
        color: #64748b;
    }

    /* Outside rate plan window — not for sale */
    .status-not-on-sale {
        background: repeating-linear-gradient(
            45deg,
            #f8f9fa,
            #f8f9fa 4px,
            #eeeeee 4px,
            #eeeeee 8px
        );
        cursor: default;
        opacity: 0.55;
    }

    .status-not-on-sale .availability-number {
        color: #aaaaaa;
        font-size: 11px;
    }

    .status-not-on-sale .availability-label {
        color: #bbbbbb;
    }

    .no-properties {
        text-align: center;
        padding: 60px 20px;
        color: #6b6b6b;
    }

    .no-properties-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    /* Availability Modal Styles */
    .availability-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .availability-modal.active {
        display: flex !important;
    }

    .modal-content-box {
        background: white;
        border-radius: 16px;
        width: 600px;
        max-width: 90%;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header-box {
        padding: 24px 32px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    }

    .modal-title-box {
        font-size: 22px;
        font-weight: 700;
        color: #262626;
        margin: 0;
    }

    .modal-close-btn {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: none;
        background: #fee2e2;
        color: #dc2626;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-weight: 700;
    }

    .modal-close-btn:hover {
        background: #dc2626;
        color: white;
        transform: rotate(90deg);
    }

    .modal-body-box {
        padding: 32px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .detail-row-primary {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 12px;
        margin-bottom: 16px;
        border: 1px solid #e5e7eb;
    }

    .detail-icon {
        font-size: 32px;
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .detail-label-modal {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value-modal {
        font-size: 18px;
        font-weight: 700;
        color: #262626;
    }

    .stats-grid-modal {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-top: 24px;
    }

    .stat-box-modal {
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .stat-box-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-box-modal.stat-available {
        border-color: #16a34a;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    }

    .stat-box-modal.stat-booked {
        border-color: #dc2626;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }

    .stat-box-modal.stat-held {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }

    .stat-icon-modal {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .stat-number-modal {
        font-size: 36px;
        font-weight: 700;
        color: #262626;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label-modal {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-footer-box {
        padding: 20px 32px;
        border-top: 2px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        background: #f9fafb;
    }

    .btn-modal {
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-secondary-modal {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-secondary-modal:hover {
        background: #d1d5db;
    }

    .btn-primary-modal {
        background: linear-gradient(135deg, #003580 0%, #00509e 100%);
        color: white;
    }

    .btn-primary-modal:hover {
        background: linear-gradient(135deg, #00509e 0%, #003580 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
    }

    @media (max-width: 768px) {
        .calendar-header {
            flex-direction: column;
        }
        
        .modal-content-box {
            width: 95%;
        }
        
        .stats-grid-modal {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="calendar-wrapper">
    <div class="calendar-header">
        <div class="calendar-controls">
            <select class="property-selector" id="propertyFilter" onchange="filterProperty()">
                <option value="">All Properties</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                @endforeach
            </select>

            <div class="month-navigation">
                <button class="nav-btn" onclick="previousMonth()">◀</button>
                <span class="current-month" id="currentMonth"></span>
                <button class="nav-btn" onclick="nextMonth()">▶</button>
            </div>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color available"></div>
                <span>Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-color held" style="background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);"></div>
                <span>Active hold (in checkout)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color booked"></div>
                <span>Filling up</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #fee2e2;"></div>
                <span>Fully booked</span>
            </div>
            <div class="legend-item">
                <div class="legend-color blocked"></div>
                <span>Blocked (maintenance)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: repeating-linear-gradient(45deg,#f8f9fa,#f8f9fa 4px,#eeeeee 4px,#eeeeee 8px); opacity:.6;"></div>
                <span>Not on sale</span>
            </div>
        </div>
    </div>

    @if($properties->isEmpty())
        <div class="no-properties">
            <div class="no-properties-icon">🏨</div>
            <h3>No Properties Found</h3>
            <p>Please create a property and room types first to view availability.</p>
        </div>
    @else
        <div class="properties-container" id="propertiesContainer">
            @foreach($properties as $property)
                <div class="property-section" data-property-id="{{ $property->id }}">
                    <div class="property-header">
                        <div>
                            <div class="property-title">{{ $property->name }}</div>
                            <div class="property-info">
                                {{ $property->location }} • {{ $property->roomTypes->count() }} Room Types • {{ $property->roomTypes->sum('total_units') }} Total Units
                            </div>
                        </div>
                    </div>
                    
                    @if($property->roomTypes->isEmpty())
                        <div style="padding: 40px; text-align: center; color: #6b7280; background: white;">
                            <div style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;">🛏️</div>
                            <p style="font-weight:600;margin-bottom:4px;">No room types with rate plans</p>
                            <p style="font-size:13px;">Add a <a href="{{ route('admin.rate-plans.create') }}" style="color:#0071c2;">Rate Plan</a> to a room type to see it here</p>
                        </div>
                    @else
                        <div class="availability-table-wrapper">
                            <table class="availability-table" id="table-{{ $property->id }}">
                                <thead>
                                    <tr>
                                        <th>Room Type</th>
                                        <!-- Date headers will be generated by JavaScript -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->roomTypes as $roomType)
                                        <tr data-room-type-id="{{ $roomType->id }}" data-total-units="{{ $roomType->total_units }}">
                                            <th>
                                                <div class="room-type-name-cell">{{ $roomType->name }}</div>
                                                <div class="room-type-units">{{ $roomType->total_units }} units • ${{ number_format($roomType->base_price, 2) }}/night</div>
                                            </th>
                                            <!-- Date cells will be generated by JavaScript -->
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Availability Detail Modal -->
<div class="availability-modal" id="availabilityModal">
    <div class="modal-content-box">
        <div class="modal-header-box">
            <h2 class="modal-title-box">📅 Availability Details</h2>
            <button class="modal-close-btn" onclick="closeAvailabilityModal()">✕</button>
        </div>
        
        <div class="modal-body-box">
            <div class="detail-row-primary">
                <div class="detail-icon">📅</div>
                <div>
                    <div class="detail-label-modal">Date</div>
                    <div class="detail-value-modal" id="modalDate">-</div>
                </div>
            </div>

            <div class="detail-row-primary">
                <div class="detail-icon">🏨</div>
                <div>
                    <div class="detail-label-modal">Property</div>
                    <div class="detail-value-modal" id="modalProperty">-</div>
                </div>
            </div>

            <div class="detail-row-primary">
                <div class="detail-icon">🛏️</div>
                <div>
                    <div class="detail-label-modal">Room Type</div>
                    <div class="detail-value-modal" id="modalRoomType">-</div>
                </div>
            </div>

            <div class="stats-grid-modal">
                <div class="stat-box-modal">
                    <div class="stat-icon-modal">🏠</div>
                    <div class="stat-number-modal" id="modalTotalUnits">0</div>
                    <div class="stat-label-modal">Total Units</div>
                </div>

                <div class="stat-box-modal stat-available">
                    <div class="stat-icon-modal">✅</div>
                    <div class="stat-number-modal" id="modalAvailable">0</div>
                    <div class="stat-label-modal">Available</div>
                </div>

                <div class="stat-box-modal stat-booked">
                    <div class="stat-icon-modal">📌</div>
                    <div class="stat-number-modal" id="modalBooked">0</div>
                    <div class="stat-label-modal">Booked</div>
                </div>

                <div class="stat-box-modal stat-held">
                    <div class="stat-icon-modal">⏳</div>
                    <div class="stat-number-modal" id="modalHeld">0</div>
                    <div class="stat-label-modal">On Hold</div>
                </div>
            </div>
        </div>

        <div class="modal-footer-box">
            <button class="btn-modal btn-secondary-modal" onclick="closeAvailabilityModal()">Close</button>
            <button class="btn-modal btn-primary-modal" onclick="showBlockModal()">🚫 Block This Date</button>
        </div>
    </div>
</div>

<!-- Block Date Modal -->
<div class="availability-modal" id="blockModal">
    <div class="modal-content-box" style="max-width: 500px;">
        <div class="modal-header-box">
            <h2 class="modal-title-box">🚫 Block Date</h2>
            <button class="modal-close-btn" onclick="closeBlockModal()">✕</button>
        </div>
        
        <div class="modal-body-box">
            <p style="margin-bottom: 20px; color: #6b7280; font-size: 14px;">
                Enter the reason for blocking this date. This will prevent new bookings.
            </p>
            
            <div class="form-group">
                <label for="blockReason" style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">
                    Reason for Blocking <span style="color: #dc2626;">*</span>
                </label>
                <input 
                    type="text" 
                    id="blockReason" 
                    class="form-control" 
                    placeholder="e.g., Maintenance, Renovation, Private Event"
                    style="width: 100%; padding: 10px 14px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 14px;"
                />
                <span id="blockReasonError" style="display: none; color: #dc2626; font-size: 12px; font-weight: 500; margin-top: 6px;">
                    Please enter a reason for blocking
                </span>
            </div>
        </div>

        <div class="modal-footer-box">
            <button class="btn-modal btn-secondary-modal" onclick="closeBlockModal()">Cancel</button>
            <button class="btn-modal btn-primary-modal" onclick="confirmBlockDate()" id="confirmBlockBtn">
                Confirm Block
            </button>
        </div>
    </div>
</div>

<script>
let currentDate = new Date();
let selectedPropertyId = '';
let bookingsData = @json($bookings);
let holdsData = @json($holds);  // Active booking holds
let blockedDatesData = @json($blockedDates);
let propertiesData = @json($properties);
let ratePlansData = @json($ratePlans);   // [{id, room_type_id, start_date, end_date}, ...]
const daysToShow = 7;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCalendarMonth();
    renderAllTables();
});

function updateCalendarMonth() {
    const startDate = new Date(currentDate);
    const endDate = new Date(currentDate);
    endDate.setDate(endDate.getDate() + 6);
    
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    const startStr = `${monthNames[startDate.getMonth()]} ${startDate.getDate()}`;
    const endStr = `${monthNames[endDate.getMonth()]} ${endDate.getDate()}, ${endDate.getFullYear()}`;
    
    document.getElementById('currentMonth').textContent = `${startStr} - ${endStr}`;
}

function previousMonth() {
    currentDate.setDate(currentDate.getDate() - 7); // Go back 1 week
    updateCalendarMonth();
    renderAllTables();
}

function nextMonth() {
    currentDate.setDate(currentDate.getDate() + 7); // Go forward 1 week
    updateCalendarMonth();
    renderAllTables();
}

function filterProperty() {
    selectedPropertyId = document.getElementById('propertyFilter').value;
    const sections = document.querySelectorAll('.property-section');
    
    sections.forEach(section => {
        const propertyId = section.getAttribute('data-property-id');
        if (selectedPropertyId === '' || propertyId == selectedPropertyId) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

function renderAllTables() {
    propertiesData.forEach(property => {
        if (property.room_types && property.room_types.length > 0) {
            renderPropertyTable(property.id);
        }
    });
}

function renderPropertyTable(propertyId) {
    const table = document.getElementById('table-' + propertyId);
    if (!table) return;

    const thead = table.querySelector('thead tr');
    const tbody = table.querySelector('tbody');
    
    // Generate dates
    const dates = [];
    for (let i = 0; i < daysToShow; i++) {
        const date = new Date(currentDate);
        date.setDate(currentDate.getDate() + i);
        dates.push(date);
    }
    
    // Update header with dates
    const firstCell = thead.cells[0];
    thead.innerHTML = '';
    thead.appendChild(firstCell);
    
    dates.forEach(date => {
        const th = document.createElement('th');
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        const dayNum = date.getDate();
        const monthName = date.toLocaleDateString('en-US', { month: 'short' });
        
        th.innerHTML = `
            <span class="date-header-main">${dayNum} ${monthName}</span>
            <span class="date-header-day">${dayName}</span>
        `;
        thead.appendChild(th);
    });
    
    // Update body rows with availability data
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const roomTypeId = row.getAttribute('data-room-type-id');
        const totalUnits = parseInt(row.getAttribute('data-total-units'));
        
        // Keep first cell (room type name)
        const firstCell = row.cells[0];
        row.innerHTML = '';
        row.appendChild(firstCell);
        
        dates.forEach(date => {
            const td = document.createElement('td');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            // Normalize to local midnight so comparisons are day-accurate
            date.setHours(0, 0, 0, 0);
            
            const isToday = date.getTime() === today.getTime();
            const dateStr = formatDate(date);
            const status = getDateStatus(date, roomTypeId, totalUnits);

            let cellClass = 'date-cell';

            // ── Not on sale (outside all rate plan windows) ──────────────────
            if (!isOnSale(date, roomTypeId)) {
                cellClass += ' status-not-on-sale';
                if (isToday) cellClass += ' today';
                td.className = cellClass;
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">—</div>
                        <div class="availability-label">n/a</div>
                    </div>
                `;
                // No click handler — cell is not interactive
                row.appendChild(td);
                return;
            }

            // ── Normal status rendering ───────────────────────────────────────
            let statusText = '';
            
            // Determine cell status class based on availability
            if (status.blocked > 0) {
                cellClass += ' status-blocked';
                statusText = 'maint.';
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">🔒</div>
                        <div class="availability-label">${statusText}</div>
                    </div>
                `;
            } else if (status.held > 0) {
                // Has active hold - show orange stripe
                cellClass += ' status-hold';
                const booked = status.booked;
                statusText = status.held === 1 ? '1 hold' : `${status.held} holds`;
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">${booked}+${status.held}</div>
                        <div class="availability-label">${statusText}</div>
                    </div>
                `;
            } else if (status.available === 0) {
                // Fully booked
                cellClass += ' status-booked';
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">${totalUnits}/${totalUnits}</div>
                        <div class="availability-label">full</div>
                    </div>
                `;
            } else if (status.booked > 0) {
                // Partially booked
                cellClass += ' status-partial';
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">${status.booked}/${totalUnits}</div>
                        <div class="availability-label">${status.available} free</div>
                    </div>
                `;
            } else {
                // Fully available
                cellClass += ' status-available';
                td.innerHTML = `
                    <div class="availability-status">
                        <div class="availability-number">0/${totalUnits}</div>
                        <div class="availability-label">${totalUnits} free</div>
                    </div>
                `;
            }
            
            if (isToday) cellClass += ' today';
            
            td.className = cellClass;
            td.onclick = function() { cellClick(dateStr, roomTypeId, status); };
            
            row.appendChild(td);
        });
    });
}

/**
 * Returns true if the given date falls within at least one active rate plan
 * for this room type, OR if the room type has no rate plans at all
 * (base price applies, always on sale).
 *
 * "On sale" means: ratePlan.start_date <= date <= ratePlan.end_date
 * (using the check-in date — if the night starting on `date` has a plan, it's on sale)
 */
function isOnSale(date, roomTypeId) {
    const plans = ratePlansData.filter(p => p.room_type_id == roomTypeId);

    // No rate plans → always on sale
    if (plans.length === 0) return true;

    return plans.some(plan => {
        const start = new Date(plan.start_date.substring(0, 10) + 'T00:00:00');
        const end   = new Date(plan.end_date.substring(0,   10) + 'T00:00:00');
        return date >= start && date <= end;
    });
}

function getDateStatus(date, roomTypeId, totalUnits) {
    let booked = 0;
    let held = 0;
    let blocked = 0;

    /**
     * Parse a date-only string safely, always as LOCAL midnight.
     * "2026-07-09 00:00:00" or "2026-07-09" → local midnight, no TZ shift.
     * Using "YYYY-MM-DDT00:00:00" forces local time in all browsers.
     */
    function parseLocalDate(str) {
        const d = str.toString().substring(0, 10); // keep "YYYY-MM-DD"
        return new Date(d + 'T00:00:00');
    }

    // Count confirmed / checked-in bookings
    // Rule: a booking occupies a date if  checkIn <= date < checkOut
    // e.g. booking 10 Jul → 12 Jul  occupies  10 Jul, 11 Jul  (NOT 12 Jul)
    bookingsData.forEach(booking => {
        if (booking.room_type_id != roomTypeId) return;

        const checkIn  = parseLocalDate(booking.check_in);
        const checkOut = parseLocalDate(booking.check_out);

        if (date >= checkIn && date < checkOut) {
            if (booking.status === 'confirmed' || booking.status === 'checked_in') {
                booked++;
            }
        }
    });

    // Count active holds (pending and not expired)
    // Holds work the same way: checkIn <= date < checkOut
    holdsData.forEach(hold => {
        if (hold.room_type_id != roomTypeId) return;

        const checkIn  = parseLocalDate(hold.check_in);
        const checkOut = parseLocalDate(hold.check_out);

        if (date >= checkIn && date < checkOut) {
            // Add num_rooms to held count (holds can be for multiple rooms)
            held += (hold.num_rooms || 1);
        }
    });

    // Count blocked dates
    // Blocks are inclusive on both ends (start_date and end_date are both blocked)
    blockedDatesData.forEach(block => {
        if (block.room_type_id != roomTypeId) return;

        const blockStart = parseLocalDate(block.start_date);
        const blockEnd   = parseLocalDate(block.end_date);

        if (date >= blockStart && date <= blockEnd) {
            blocked = 1;
        }
    });

    const available = totalUnits - blocked - booked - held;

    return {
        available: Math.max(0, available),
        booked,
        held,
        blocked,
        total: totalUnits
    };
}

let currentCellData = null; // Store current cell data for blocking

function cellClick(date, roomTypeId, status) {
    const roomType = propertiesData
        .flatMap(p => p.room_types)
        .find(rt => rt.id == roomTypeId);
    
    if (!roomType) return;
    
    const dateObj = new Date(date);
    const dateFormatted = dateObj.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    const property = propertiesData.find(p => 
        p.room_types.some(rt => rt.id == roomTypeId)
    );
    
    // Store current cell data for blocking
    currentCellData = {
        date: date,
        dateFormatted: dateFormatted,
        roomTypeId: roomTypeId,
        roomTypeName: roomType.name,
        propertyName: property ? property.name : 'N/A',
        totalUnits: status.total,
        available: status.available,
        booked: status.booked,
        held: status.held,
        blocked: status.blocked
    };
    
    // Show modal with information
    showAvailabilityModal(currentCellData);
}

function showAvailabilityModal(data) {
    const modal = document.getElementById('availabilityModal');
    
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }
    
    // Update modal content
    document.getElementById('modalDate').textContent = data.dateFormatted;
    document.getElementById('modalRoomType').textContent = data.roomTypeName;
    document.getElementById('modalProperty').textContent = data.propertyName;
    document.getElementById('modalTotalUnits').textContent = data.totalUnits;
    document.getElementById('modalAvailable').textContent = data.available;
    document.getElementById('modalBooked').textContent = data.booked;
    document.getElementById('modalHeld').textContent = data.held;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeAvailabilityModal() {
    const modal = document.getElementById('availabilityModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
}

// Close modal when clicking outside
window.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('availabilityModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAvailabilityModal();
            }
        });
    }
});

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Show blocking modal
 */
function showBlockModal() {
    const modal = document.getElementById('blockModal');
    const inputField = document.getElementById('blockReason');
    const errorSpan = document.getElementById('blockReasonError');
    
    // Clear previous input and errors
    inputField.value = '';
    errorSpan.style.display = 'none';
    inputField.style.borderColor = '#d1d5db';
    
    // Show modal
    modal.classList.add('active');
    
    // Focus on input field
    setTimeout(() => {
        inputField.focus();
    }, 100);
    
    // Add input listener to hide error on typing
    inputField.addEventListener('input', function() {
        if (this.value.trim()) {
            errorSpan.style.display = 'none';
            this.style.borderColor = '#d1d5db';
        }
    });
}

/**
 * Close blocking modal
 */
function closeBlockModal() {
    const modal = document.getElementById('blockModal');
    modal.classList.remove('active');
}

/**
 * Confirm and execute the block
 */
function confirmBlockDate() {
    if (!currentCellData) {
        alert('No date selected');
        return;
    }
    
    const reasonInput = document.getElementById('blockReason');
    const errorSpan = document.getElementById('blockReasonError');
    const reason = reasonInput.value.trim();
    
    // Hide previous error
    errorSpan.style.display = 'none';
    reasonInput.style.borderColor = '#d1d5db';
    
    // Validate
    if (!reason) {
        // Show inline error
        errorSpan.style.display = 'block';
        reasonInput.style.borderColor = '#dc2626';
        reasonInput.focus();
        return;
    }
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmBlockBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Blocking...';
    confirmBtn.disabled = true;
    
    // Send AJAX request to block the date
    fetch('{{ route("admin.availability.block") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            room_type_id: currentCellData.roomTypeId,
            start_date: currentCellData.date,
            end_date: currentCellData.date, // Block single date
            reason: reason
        })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Please check server logs.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close both modals
            closeBlockModal();
            closeAvailabilityModal();
            
            // Show success toast
            if (typeof showToast === 'function') {
                showToast('Date blocked successfully!', 'success');
            }
            
            // Reload page to refresh calendar
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Failed to block date');
        }
    })
    .catch(error => {
        console.error('Error blocking date:', error);
        alert('Error: ' + error.message);
        
        // Reset button state
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

</script>
@endsection
