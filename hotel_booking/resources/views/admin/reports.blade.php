@extends('admin.layout')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports & Analytics')

@section('content')
<style>
    /* Date Range Filter */
    .date-filter {
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        display: flex;
        gap: 16px;
        align-items: end;
    }

    .date-group {
        display: flex;
        flex-direction: column;
    }

    .date-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .form-control {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        color: #1f2937;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-filter {
        padding: 10px 24px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 42px;
    }

    .btn-filter:hover {
        background: #2563eb;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .quick-filters {
        display: flex;
        gap: 8px;
    }

    .btn-quick {
        padding: 8px 16px;
        background: white;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-quick:hover,
    .btn-quick.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .kpi-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .kpi-card:nth-child(1) { border-left-color: #3b82f6; }
    .kpi-card:nth-child(2) { border-left-color: #10b981; }
    .kpi-card:nth-child(3) { border-left-color: #f59e0b; }
    .kpi-card:nth-child(4) { border-left-color: #8b5cf6; }

    .kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .kpi-change {
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .kpi-change.positive { color: #10b981; }
    .kpi-change.negative { color: #ef4444; }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .chart-card.full-width {
        grid-column: 1 / -1;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .chart-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-subtitle {
        font-size: 11px;
        color: #6b7280;
    }

    .chart-body {
        position: relative;
        height: 300px;
    }

    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #f9fafb;
    }

    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        color: #1f2937;
    }

    .data-table tbody tr:hover {
        background: #f9fafb;
    }

    /* Progress Bars */
    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }

    .progress-fill {
        height: 100%;
        background: #3b82f6;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* Number Display */
    .metric-number {
        font-weight: 600;
        color: #1f2937;
    }

    .metric-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Empty State */
    .empty-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 300px;
        color: #9ca3af;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .empty-text {
        font-size: 14px;
        font-weight: 600;
    }
</style>

<!-- Date Range Filter -->
<div class="date-filter">
    <form method="GET" action="{{ route('admin.reports') }}" id="reportsForm" style="display: flex; gap: 16px; align-items: end; flex: 1;">
        <div class="date-group">
            <label class="date-label">From Date</label>
            <input type="date" name="from_date" id="fromDateInput" class="form-control" value="{{ $fromDate }}">
        </div>
        <div class="date-group">
            <label class="date-label">To Date</label>
            <input type="date" name="to_date" id="toDateInput" class="form-control" value="{{ $toDate }}">
        </div>
        <button type="submit" class="btn-filter">Apply</button>
    </form>
    
    <div class="quick-filters">
        <button class="btn-quick" onclick="setDateRange('week', this)">This Week</button>
        <button class="btn-quick" onclick="setDateRange('month', this)">This Month</button>
        <button class="btn-quick" onclick="setDateRange('year', this)">This Year</button>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Occupancy Rate</div>
        <div class="kpi-value">{{ number_format($metrics['occupancy_rate'], 1) }}%</div>
        <div class="kpi-change {{ $metrics['occupancy_change'] >= 0 ? 'positive' : 'negative' }}">
            {{ $metrics['occupancy_change'] >= 0 ? '▲' : '▼' }}
            {{ abs(number_format($metrics['occupancy_change'], 1)) }}% vs last period
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-label">RevPAR</div>
        <div class="kpi-value">£{{ number_format($metrics['revpar'], 2) }}</div>
        <div class="kpi-change {{ $metrics['revpar_change'] >= 0 ? 'positive' : 'negative' }}">
            {{ $metrics['revpar_change'] >= 0 ? '▲' : '▼' }}
            {{ abs(number_format($metrics['revpar_change'], 1)) }}% vs last period
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value">£{{ number_format($metrics['total_revenue'], 0) }}</div>
        <div class="kpi-change {{ $metrics['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
            {{ $metrics['revenue_change'] >= 0 ? '▲' : '▼' }}
            {{ abs(number_format($metrics['revenue_change'], 1)) }}% vs last period
        </div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-label">Avg. Daily Rate</div>
        <div class="kpi-value">£{{ number_format($metrics['adr'], 2) }}</div>
        <div class="kpi-change {{ $metrics['adr_change'] >= 0 ? 'positive' : 'negative' }}">
            {{ $metrics['adr_change'] >= 0 ? '▲' : '▼' }}
            {{ abs(number_format($metrics['adr_change'], 1)) }}% vs last period
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <!-- Revenue Over Time -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <div>
                <div class="chart-title">Revenue Over Time</div>
                <div class="chart-subtitle">Daily revenue trend for selected period</div>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <!-- Booking Sources -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Booking Sources</div>
                <div class="chart-subtitle">Where bookings come from</div>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="sourcesChart"></canvas>
        </div>
    </div>
    
    <!-- Top Room Types -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Top Room Types</div>
                <div class="chart-subtitle">Most booked room types</div>
            </div>
        </div>
        <div class="chart-body">
            @if(count($topRoomTypes) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Room Type</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topRoomTypes as $room)
                        <tr>
                            <td>
                                <div class="metric-number">{{ $room['name'] }}</div>
                                <div class="metric-label">{{ $room['property'] }}</div>
                            </td>
                            <td>{{ $room['bookings'] }}</td>
                            <td>£{{ number_format($room['revenue'], 2) }}</td>
                            <td>
                                <div>{{ number_format($room['percentage'], 1) }}%</div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $room['percentage'] }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-chart">
                    <div class="empty-icon">📊</div>
                    <div class="empty-text">No data available</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Occupancy by Property -->
<div class="chart-card">
    <div class="chart-header">
        <div>
            <div class="chart-title">Occupancy by Property</div>
            <div class="chart-subtitle">Performance breakdown per property</div>
        </div>
    </div>
    <div class="chart-body">
        @if(count($propertyOccupancy) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Total Units</th>
                        <th>Booked Units</th>
                        <th>Occupancy Rate</th>
                        <th>Revenue</th>
                        <th>RevPAR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($propertyOccupancy as $property)
                    <tr>
                        <td>
                            <div class="metric-number">{{ $property['name'] }}</div>
                            <div class="metric-label">{{ $property['location'] }}</div>
                        </td>
                        <td>{{ $property['total_units'] }}</td>
                        <td>{{ $property['booked_units'] }}</td>
                        <td>
                            <div class="metric-number">{{ number_format($property['occupancy_rate'], 1) }}%</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $property['occupancy_rate'] }}%; background: {{ $property['occupancy_rate'] >= 80 ? '#10b981' : ($property['occupancy_rate'] >= 50 ? '#f59e0b' : '#ef4444') }}"></div>
                            </div>
                        </td>
                        <td>£{{ number_format($property['revenue'], 2) }}</td>
                        <td>£{{ number_format($property['revpar'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-chart">
                <div class="empty-icon">🏨</div>
                <div class="empty-text">No property data available</div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Wait for Chart.js to load
    window.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing charts...');
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        
        // Revenue Over Time Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            const revenueLabels = {!! json_encode($revenueData['labels']) !!};
            const revenueValues = {!! json_encode($revenueData['values']) !!};
            
            console.log('Revenue Chart Data:', {
                labels: revenueLabels,
                values: revenueValues
            });
            
            try {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Revenue (£)',
                            data: revenueValues,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: £' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: true,
                                    drawOnChartArea: true,
                                    drawTicks: true
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '£' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: true,
                                    drawOnChartArea: true,
                                    drawTicks: true
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
                console.log('Revenue chart created successfully');
            } catch (error) {
                console.error('Error creating revenue chart:', error);
            }
        } else {
            console.error('Revenue chart canvas not found');
        }

        // Booking Sources Chart
        const sourcesCtx = document.getElementById('sourcesChart');
        if (sourcesCtx) {
            const sourcesLabels = {!! json_encode($bookingSources['labels']) !!};
            const sourcesValues = {!! json_encode($bookingSources['values']) !!};
            
            console.log('Sources Chart Data:', {
                labels: sourcesLabels,
                values: sourcesValues
            });
            
            // Check if we have any data
            const hasData = sourcesValues.some(val => val > 0);
            
            if (!hasData) {
                // Show empty state instead of chart
                sourcesCtx.parentElement.innerHTML = `
                    <div class="empty-chart">
                        <div class="empty-icon">📊</div>
                        <div class="empty-text">No booking source data available</div>
                    </div>
                `;
                console.log('No booking source data - showing empty state');
            } else {
                try {
                    new Chart(sourcesCtx, {
                        type: 'doughnut',
                        data: {
                            labels: sourcesLabels,
                            datasets: [{
                                data: sourcesValues,
                                backgroundColor: [
                                    '#3b82f6',
                                    '#10b981',
                                    '#f59e0b',
                                    '#8b5cf6',
                                    '#ef4444'
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                    console.log('Sources chart created successfully');
                } catch (error) {
                    console.error('Error creating sources chart:', error);
                }
            }
        } else {
            console.error('Sources chart canvas not found');
        }
        
        console.log('Charts initialization complete');
    });

    // Quick Date Range Filters
    function setDateRange(range) {
        const today = new Date();
        const pad = n => String(n).padStart(2, '0');
        const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

        let fromDate, toDate;

        console.log('Setting date range:', range);

        switch(range) {
            case 'today':
                fromDate = fmt(today);
                toDate   = fmt(today);
                break;

            case 'week': {
                const mon = new Date(today);
                mon.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1));
                const sun = new Date(mon);
                sun.setDate(mon.getDate() + 6);
                fromDate = fmt(mon);
                toDate   = fmt(sun);
                break;
            }

            case 'month': {
                const first = new Date(today.getFullYear(), today.getMonth(), 1);
                const last  = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                fromDate = fmt(first);
                toDate   = fmt(last);
                break;
            }

            case 'year': {
                fromDate = `${today.getFullYear()}-01-01`;
                toDate   = `${today.getFullYear()}-12-31`;
                break;
            }
        }

        console.log('Date range set:', fromDate, 'to', toDate);

        document.getElementById('fromDateInput').value = fromDate;
        document.getElementById('toDateInput').value   = toDate;
        
        // Add visual feedback
        const buttons = document.querySelectorAll('.btn-quick');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Submit the form
        document.getElementById('reportsForm').submit();
    }
</script>

@endsection
