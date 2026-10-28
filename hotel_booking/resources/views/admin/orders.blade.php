@extends('admin.layout')

@section('title', 'Orders - Financial View')
@section('page-title', 'Orders & Payments')

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
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        border-left: 4px solid #16a34a;
    }
    .stat-card.stat-total   { border-left-color: #0071c2; }
    .stat-card.stat-pending { border-left-color: #f59e0b; }
    .stat-label { font-size:13px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px; }
    .stat-value { font-size:32px;font-weight:700;color:#262626; }
    .stat-value.revenue { color:#16a34a; }

    .filters-section {
        background:white;border-radius:12px;padding:24px;
        margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.1);
    }
    .filters-grid {
        display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;
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
        padding:10px 24px;background:#0071c2;color:white;border:none;
        border-radius:8px;font-weight:600;cursor:pointer;height:42px;
        transition:all .2s;
    }
    .btn-filter:hover { background:#00509e; }

    .orders-table-wrapper {
        background:white;border-radius:12px;
        box-shadow:0 1px 3px rgba(0,0,0,.1);overflow:hidden;
    }
    .orders-table { width:100%;border-collapse:collapse; }
    .orders-table thead { background:#f9fafb;border-bottom:2px solid #e5e7eb; }
    .orders-table th {
        padding:12px 14px;text-align:left;font-size:10px;font-weight:700;
        color:#6b7280;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;
    }
    .orders-table td {
        padding:14px;border-bottom:1px solid #f3f4f6;
        font-size:13px;color:#262626;vertical-align:middle;
    }
    .orders-table tbody tr { transition:all .2s;cursor:pointer; }
    .orders-table tbody tr:hover { background:#f0f7ff; }

    .order-number { font-weight:700;color:#0071c2;font-size:13px;margin-bottom:3px; }
    .order-date   { font-size:11px;color:#9ca3af; }
    .guest-name   { font-weight:600;font-size:13px;margin-bottom:2px; }
    .booking-ref  { font-size:11px;color:#6b7280; }

    .badge { display:inline-block;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;text-transform:uppercase; }
    .badge-paid     { background:#dcfce7;color:#166534; }
    .badge-pending  { background:#fef3c7;color:#a16207; }
    .badge-failed   { background:#fee2e2;color:#991b1b; }
    .badge-refunded { background:#f3f4f6;color:#6b7280; }
    .badge-cancelled{ background:#fce7f3;color:#9d174d; }

    .amount-col { font-weight:700;font-size:14px;color:#16a34a;font-family:'Courier New',monospace; }

    .btn-view {
        padding:6px 14px;background:#0071c2;color:white;border:none;
        border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;
        transition:all .2s;white-space:nowrap;
    }
    .btn-view:hover { background:#00509e; }

    .no-results { text-align:center;padding:60px 20px;color:#6b7280; }
    .pagination-wrapper {
        padding:20px 24px;display:flex;justify-content:space-between;
        align-items:center;border-top:2px solid #e5e7eb;background:#f9fafb;
    }
    .pagination-info { font-size:13px;color:#6b7280;font-weight:600; }

    /* ── Right-side Slide Panel ── */
    .order-panel-overlay {
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);
        z-index:3000;backdrop-filter:blur(2px);
    }
    .order-panel-overlay.open { display:block; }
    .order-panel {
        position:fixed;top:0;right:-560px;width:560px;max-width:95vw;height:100%;
        background:white;box-shadow:-8px 0 40px rgba(0,0,0,.15);
        z-index:3001;transition:right .3s ease;overflow-y:auto;
        display:flex;flex-direction:column;
    }
    .order-panel.open { right:0; }

    .panel-header {
        display:flex;align-items:center;justify-content:space-between;
        padding:20px 24px;border-bottom:2px solid #e5e7eb;
        background:linear-gradient(135deg,#003580,#0071c2);color:white;
        position:sticky;top:0;z-index:10;flex-shrink:0;
    }
    .panel-header h2 { margin:0;font-size:18px;font-weight:700; }
    .panel-header p  { margin:4px 0 0;font-size:12px;opacity:.85; }
    .panel-close {
        width:36px;height:36px;border:2px solid rgba(255,255,255,.4);
        background:transparent;color:white;border-radius:8px;font-size:18px;
        cursor:pointer;display:flex;align-items:center;justify-content:center;
        transition:all .2s;flex-shrink:0;
    }
    .panel-close:hover { background:rgba(255,255,255,.2); }

    .panel-body { padding:24px;flex:1; }

    .panel-section {
        background:#f9fafb;border-radius:10px;padding:16px;
        margin-bottom:16px;border:1px solid #e5e7eb;
    }
    .panel-section-title {
        font-size:11px;font-weight:700;text-transform:uppercase;
        letter-spacing:.6px;color:#6b7280;margin-bottom:12px;
        display:flex;align-items:center;gap:6px;
    }
    .panel-row {
        display:flex;justify-content:space-between;align-items:flex-start;
        padding:6px 0;font-size:13px;border-bottom:1px solid #eeeeee;
    }
    .panel-row:last-child { border-bottom:none; }
    .panel-label { color:#6b7280;font-weight:500; }
    .panel-value { color:#111;font-weight:600;text-align:right;max-width:60%; }

    .panel-total-row {
        display:flex;justify-content:space-between;
        padding:10px 0;font-size:15px;font-weight:700;
        border-top:2px solid #262626;margin-top:6px;
    }
    .panel-total-value { color:#16a34a;font-size:18px; }

    .panel-badge { display:inline-block;padding:5px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase; }

    @media(max-width:768px) {
        .stats-grid { grid-template-columns:1fr 1fr; }
        .filters-grid { grid-template-columns:1fr; }
    }
</style>
@endsection

@section('content')

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value revenue">£{{ number_format($stats['total_revenue'],2) }}</div>
    </div>
    <div class="stat-card stat-total">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ $stats['total_orders'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Paid Orders</div>
        <div class="stat-value">{{ $stats['paid_orders'] }}</div>
    </div>
    <div class="stat-card stat-pending">
        <div class="stat-label">Pending Orders</div>
        <div class="stat-value">{{ $stats['pending_orders'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="filters-section">
    <form method="GET" action="{{ route('admin.orders') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Search Order / Guest</label>
                <input type="text" name="search" class="filter-input"
                       placeholder="Order number, guest name, email..."
                       value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">Payment Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="paid"      {{ request('status')=='paid'      ? 'selected':'' }}>Paid</option>
                    <option value="pending"   {{ request('status')=='pending'   ? 'selected':'' }}>Pending</option>
                    <option value="failed"    {{ request('status')=='failed'    ? 'selected':'' }}>Failed</option>
                    <option value="refunded"  {{ request('status')=='refunded'  ? 'selected':'' }}>Refunded</option>
                    <option value="cancelled" {{ request('status')=='cancelled' ? 'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">From Date</label>
                <input type="date" name="from_date" class="filter-input" value="{{ request('from_date') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input type="date" name="to_date" class="filter-input" value="{{ request('to_date') }}">
            </div>
            <div class="filter-group" style="display:flex;align-items:end;">
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="orders-table-wrapper">
    @if($orders->count() > 0)
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Guest</th>
                    <th>Property / Room</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr onclick="openOrderPanel({{ $order->id }})">
                    <td>
                        <div class="order-number">{{ $order->order_number }}</div>
                        <div class="order-date">{{ $order->created_at->format('M d, Y') }}</div>
                    </td>
                    <td>
                        <div class="guest-name">{{ $order->booking->guest->first_name }} {{ $order->booking->guest->last_name }}</div>
                        <div class="booking-ref">{{ $order->booking->guest->email }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $order->booking->property->name }}</div>
                        <div style="font-size:11px;color:#6b7280;">{{ $order->booking->roomType->name }}</div>
                    </td>
                    <td>
                        <div style="font-size:12px;font-weight:600;">
                            {{ \Carbon\Carbon::parse($order->booking->check_in)->format('M d') }}
                            →
                            {{ \Carbon\Carbon::parse($order->booking->check_out)->format('M d, Y') }}
                        </div>
                        <div style="font-size:11px;color:#6b7280;">
                            {{ \Carbon\Carbon::parse($order->booking->check_in)->diffInDays($order->booking->check_out) }} nights
                        </div>
                    </td>
                    <td>
                        @php $st = $order->status; @endphp
                        @if($st === 'paid')
                            <span class="badge badge-paid">✓ Paid</span>
                        @elseif($st === 'pending')
                            <span class="badge badge-pending">⏳ Pending</span>
                        @elseif($st === 'failed')
                            <span class="badge badge-failed">✕ Failed</span>
                        @elseif($st === 'refunded')
                            <span class="badge badge-refunded">↩ Refunded</span>
                        @else
                            <span class="badge badge-cancelled">✕ {{ ucfirst($st) }}</span>
                        @endif
                    </td>
                    <td class="amount-col">£{{ number_format($order->grand_total_inc_vat,2) }}</td>
                    <td onclick="event.stopPropagation()">
                        <button class="btn-view" onclick="openOrderPanel({{ $order->id }})">View Details</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
            {{ $orders->links() }}
        </div>
    @else
        <div class="no-results">
            <div style="font-size:56px;margin-bottom:16px;opacity:.3;">📋</div>
            <div style="font-size:18px;font-weight:600;margin-bottom:8px;">No Orders Found</div>
            <div style="font-size:14px;color:#9ca3af;">
                {{ request()->hasAny(['search','status','from_date','to_date']) ? 'Try adjusting your filters' : 'No orders have been placed yet' }}
            </div>
        </div>
    @endif
</div>

{{-- ── Right-side Panel ── --}}
<div class="order-panel-overlay" id="panelOverlay" onclick="closeOrderPanel()"></div>

<div class="order-panel" id="orderPanel">
    <div class="panel-header">
        <div>
            <h2>Order Details</h2>
            <p id="panelOrderNumber">Loading...</p>
        </div>
        <button class="panel-close" onclick="closeOrderPanel()">✕</button>
    </div>
    <div class="panel-body" id="panelBody">
        <div style="text-align:center;padding:60px 0;color:#9ca3af;">
            <div style="font-size:40px;margin-bottom:12px;">⏳</div>
            <div>Loading order details...</div>
        </div>
    </div>
</div>

{{-- Embed all order data as JSON for fast panel rendering (no extra AJAX needed) --}}
<script>
const ordersData = {
    @foreach($orders as $order)
    {{ $order->id }}: {
        order_number: "{{ $order->order_number }}",
        created_at:   "{{ $order->created_at->format('M d, Y · g:i A') }}",
        status:       "{{ $order->status }}",
        currency:     "{{ $order->currency }}",
        payment_method: "{{ ucfirst($order->payment_method ?? 'Card') }}",
        stripe_id:    "{{ $order->stripe_payment_intent_id ?? '' }}",
        subtotal:     {{ $order->subtotal_ex_vat ?? 0 }},
        tax_rate:     {{ $order->tax_rate ?? 0 }},
        vat_total:    {{ $order->vat_total ?? 0 }},
        service_fee:  {{ $order->service_fee ?? 0 }},
        processing_fee: {{ $order->processing_fee ?? 0 }},
        grand_total:  {{ $order->grand_total_inc_vat ?? 0 }},
        guest: {
            name:  "{{ addslashes($order->booking->guest->first_name . ' ' . $order->booking->guest->last_name) }}",
            email: "{{ $order->booking->guest->email }}",
            phone: "{{ $order->booking->guest->phone ?? '—' }}",
        },
        booking: {
            reference:  "{{ $order->booking->booking_reference }}",
            status:     "{{ $order->booking->status }}",
            check_in:   "{{ \Carbon\Carbon::parse($order->booking->check_in)->format('M d, Y') }}",
            check_out:  "{{ \Carbon\Carbon::parse($order->booking->check_out)->format('M d, Y') }}",
            nights:     {{ \Carbon\Carbon::parse($order->booking->check_in)->diffInDays($order->booking->check_out) }},
            num_guests: {{ $order->booking->num_guests }},
            property:   "{{ addslashes($order->booking->property->name) }}",
            location:   "{{ addslashes($order->booking->property->location ?? '') }}",
            room_type:  "{{ addslashes($order->booking->roomType->name) }}",
        },
        invoice: {
            number: "{{ $order->invoice->invoice_number ?? '' }}",
            status: "{{ $order->invoice->status ?? '' }}",
            id:     {{ $order->invoice->id ?? 'null' }},
        },
        items: [
            @foreach($order->items as $item)
            {
                description: "{{ addslashes($item->description) }}",
                qty:         {{ $item->qty }},
                unit_price:  {{ $item->unit_price }},
                line_total:  {{ $item->line_total }},
                item_type:   "{{ $item->item_type }}",
            },
            @endforeach
        ],
    },
    @endforeach
};

function fmt(n) {
    return '£' + parseFloat(n).toFixed(2);
}

function statusBadge(st) {
    const map = {
        paid:      ['badge-paid',      '✓ Paid'],
        pending:   ['badge-pending',   '⏳ Pending'],
        failed:    ['badge-failed',    '✕ Failed'],
        refunded:  ['badge-refunded',  '↩ Refunded'],
        confirmed: ['badge-paid',      '✓ Confirmed'],
        cancelled: ['badge-cancelled', '✕ Cancelled'],
        checked_in:  ['badge-paid',    '✓ Checked In'],
        checked_out: ['badge-refunded','↩ Checked Out'],
    };
    const [cls, label] = map[st] || ['badge-pending', st];
    return `<span class="panel-badge ${cls}">${label}</span>`;
}

function openOrderPanel(orderId) {
    const o = ordersData[orderId];
    if (!o) return;

    document.getElementById('panelOrderNumber').textContent = o.order_number + ' · ' + o.created_at;

    const itemsHtml = o.items.length
        ? o.items.map(i => `
            <div class="panel-row">
                <span class="panel-label">${i.description}</span>
                <span class="panel-value">${i.qty} × ${fmt(i.unit_price)} = ${fmt(i.line_total)}</span>
            </div>`).join('')
        : '<div class="panel-row"><span class="panel-label" style="color:#9ca3af;">No line items</span></div>';

    const invoiceBtn = o.invoice.id
        ? `<a href="/admin/invoices/${o.invoice.id}" target="_blank"
                style="display:inline-block;margin-top:12px;padding:8px 16px;
                background:#0071c2;color:white;border-radius:6px;text-decoration:none;
                font-size:12px;font-weight:600;">
              View Invoice ${o.invoice.number}
           </a>`
        : '';

    document.getElementById('panelBody').innerHTML = `
        {{-- Order Info --}}
        <div class="panel-section">
            <div class="panel-section-title">📋 Order Information</div>
            <div class="panel-row"><span class="panel-label">Order Number</span><span class="panel-value">${o.order_number}</span></div>
            <div class="panel-row"><span class="panel-label">Created</span><span class="panel-value">${o.created_at}</span></div>
            <div class="panel-row"><span class="panel-label">Payment Status</span><span class="panel-value">${statusBadge(o.status)}</span></div>
            <div class="panel-row"><span class="panel-label">Payment Method</span><span class="panel-value">${o.payment_method}</span></div>
            ${o.stripe_id ? `<div class="panel-row"><span class="panel-label">Stripe ID</span><span class="panel-value" style="font-size:10px;font-family:monospace;word-break:break-all;">${o.stripe_id}</span></div>` : ''}
        </div>

        {{-- Guest --}}
        <div class="panel-section">
            <div class="panel-section-title">👤 Guest Information</div>
            <div class="panel-row"><span class="panel-label">Name</span><span class="panel-value">${o.guest.name}</span></div>
            <div class="panel-row"><span class="panel-label">Email</span><span class="panel-value">${o.guest.email}</span></div>
            <div class="panel-row"><span class="panel-label">Phone</span><span class="panel-value">${o.guest.phone}</span></div>
        </div>

        {{-- Booking --}}
        <div class="panel-section">
            <div class="panel-section-title">🏨 Booking Details</div>
            <div class="panel-row"><span class="panel-label">Reference</span><span class="panel-value">${o.booking.reference}</span></div>
            <div class="panel-row"><span class="panel-label">Status</span><span class="panel-value">${statusBadge(o.booking.status)}</span></div>
            <div class="panel-row"><span class="panel-label">Property</span><span class="panel-value">${o.booking.property}</span></div>
            <div class="panel-row"><span class="panel-label">Room Type</span><span class="panel-value">${o.booking.room_type}</span></div>
            <div class="panel-row"><span class="panel-label">Check-in</span><span class="panel-value">${o.booking.check_in}</span></div>
            <div class="panel-row"><span class="panel-label">Check-out</span><span class="panel-value">${o.booking.check_out}</span></div>
            <div class="panel-row"><span class="panel-label">Nights</span><span class="panel-value">${o.booking.nights}</span></div>
            <div class="panel-row"><span class="panel-label">Guests</span><span class="panel-value">${o.booking.num_guests}</span></div>
        </div>

        {{-- Line Items --}}
        <div class="panel-section">
            <div class="panel-section-title">📦 Line Items</div>
            ${itemsHtml}
        </div>

        {{-- Financials --}}
        <div class="panel-section">
            <div class="panel-section-title">💰 Financial Breakdown</div>
            <div class="panel-row"><span class="panel-label">Subtotal (ex-VAT)</span><span class="panel-value">${fmt(o.subtotal)}</span></div>
            <div class="panel-row"><span class="panel-label">VAT @${o.tax_rate}%</span><span class="panel-value">${fmt(o.vat_total)}</span></div>
            ${o.service_fee > 0   ? `<div class="panel-row"><span class="panel-label">Service Fee</span><span class="panel-value">${fmt(o.service_fee)}</span></div>` : ''}
            ${o.processing_fee > 0? `<div class="panel-row"><span class="panel-label">Processing Fee</span><span class="panel-value">${fmt(o.processing_fee)}</span></div>` : ''}
            <div class="panel-total-row">
                <span>Grand Total (inc. VAT)</span>
                <span class="panel-total-value">${fmt(o.grand_total)}</span>
            </div>
            ${invoiceBtn}
        </div>
    `;

    document.getElementById('panelOverlay').classList.add('open');
    document.getElementById('orderPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeOrderPanel() {
    document.getElementById('panelOverlay').classList.remove('open');
    document.getElementById('orderPanel').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOrderPanel(); });
</script>
@endsection
