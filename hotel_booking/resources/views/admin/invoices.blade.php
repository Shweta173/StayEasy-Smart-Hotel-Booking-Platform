@extends('admin.layout')
@section('title', 'Invoices')
@section('page-title', 'Invoices Management')

@section('styles')
<style>
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;}
.stat-card{background:white;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.1);border-left:4px solid #16a34a;}
.stat-card.stat-issued{border-left-color:#0071c2;}.stat-card.stat-draft{border-left-color:#9ca3af;}
.stat-label{font-size:13px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
.stat-value{font-size:32px;font-weight:700;color:#262626;}.stat-value.amount{color:#16a34a;}
.filters-section{background:white;border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.1);}
.filters-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end;}
.filter-group{display:flex;flex-direction:column;gap:6px;}
.filter-label{font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;}
.filter-input,.filter-select{padding:10px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;background:white;}
.filter-input:focus,.filter-select:focus{outline:none;border-color:#0071c2;}
.btn-filter{padding:10px 24px;background:#0071c2;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;height:42px;}
.btn-filter:hover{background:#00509e;}
.invoices-table-wrapper{background:white;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.1);overflow:hidden;}
.invoices-table{width:100%;border-collapse:collapse;}
.invoices-table thead{background:#f9fafb;border-bottom:2px solid #e5e7eb;}
.invoices-table th{padding:12px 14px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}
.invoices-table td{padding:14px;border-bottom:1px solid #f3f4f6;font-size:13px;color:#262626;vertical-align:middle;}
.invoices-table tbody tr{cursor:pointer;transition:background .15s;}
.invoices-table tbody tr:hover{background:#f0f7ff;}
.inv-num{font-weight:700;color:#0071c2;font-size:13px;margin-bottom:3px;}
.inv-date{font-size:11px;color:#9ca3af;}
.badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:700;text-transform:uppercase;}
.badge-paid{background:#dcfce7;color:#166534;}.badge-issued{background:#dbeafe;color:#1e40af;}
.badge-draft{background:#f3f4f6;color:#6b7280;}.badge-void{background:#fee2e2;color:#991b1b;}
.amount-val{font-size:15px;font-weight:700;color:#16a34a;font-family:'Courier New',monospace;}
.btn-view{padding:6px 14px;background:#0071c2;color:white;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;}
.btn-view:hover{background:#00509e;}
.no-results{text-align:center;padding:60px 20px;color:#6b7280;}
.pagination-wrapper{padding:20px 24px;display:flex;justify-content:space-between;align-items:center;border-top:2px solid #e5e7eb;background:#f9fafb;}
.pagination-info{font-size:13px;color:#6b7280;font-weight:600;}
/* Panel */
.inv-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:3000;backdrop-filter:blur(2px);}
.inv-overlay.open{display:block;}
.inv-panel{position:fixed;top:0;right:-600px;width:600px;max-width:96vw;height:100%;background:white;box-shadow:-8px 0 40px rgba(0,0,0,.15);z-index:3001;transition:right .3s ease;overflow-y:auto;display:flex;flex-direction:column;}
.inv-panel.open{right:0;}
.inv-panel-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:2px solid #e5e7eb;background:linear-gradient(135deg,#003580,#0071c2);color:white;position:sticky;top:0;z-index:10;flex-shrink:0;}
.inv-panel-header h2{margin:0;font-size:18px;font-weight:700;}
.inv-panel-header p{margin:4px 0 0;font-size:12px;opacity:.85;}
.panel-close{width:36px;height:36px;border:2px solid rgba(255,255,255,.4);background:transparent;color:white;border-radius:8px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.panel-close:hover{background:rgba(255,255,255,.2);}
.inv-panel-body{padding:24px;flex:1;}
.p-section{background:#f9fafb;border-radius:10px;padding:16px;margin-bottom:16px;border:1px solid #e5e7eb;}
.p-section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#6b7280;margin-bottom:12px;}
.p-row{display:flex;justify-content:space-between;align-items:flex-start;padding:6px 0;font-size:13px;border-bottom:1px solid #eee;}
.p-row:last-child{border-bottom:none;}
.p-label{color:#6b7280;font-weight:500;}
.p-value{color:#111;font-weight:600;text-align:right;max-width:60%;}
.p-total{display:flex;justify-content:space-between;padding:10px 0;font-size:15px;font-weight:700;border-top:2px solid #262626;margin-top:6px;}
.p-total-val{color:#16a34a;font-size:18px;}
.p-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;}
.p-btn{padding:9px 18px;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
.p-btn-blue{background:#0071c2;color:white;}.p-btn-blue:hover{background:#00509e;}
.p-btn-green{background:#dcfce7;color:#166534;}.p-btn-green:hover{background:#bbf7d0;}
.p-btn-teal{background:#d1fae5;color:#065f46;}.p-btn-teal:hover{background:#a7f3d0;}
.p-btn-red{background:#fee2e2;color:#991b1b;}.p-btn-red:hover{background:#fecaca;}
.p-badge{display:inline-block;padding:5px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;}
@media(max-width:768px){.stats-grid{grid-template-columns:1fr 1fr;}.filters-grid{grid-template-columns:1fr;}}
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Invoiced</div><div class="stat-value amount">£{{ number_format($stats['total_invoiced'],2) }}</div></div>
    <div class="stat-card stat-issued"><div class="stat-label">Total Invoices</div><div class="stat-value">{{ $stats['total_invoices'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Paid Invoices</div><div class="stat-value">{{ $stats['paid_invoices'] }}</div></div>
    <div class="stat-card stat-draft"><div class="stat-label">Issued Invoices</div><div class="stat-value">{{ $stats['issued_invoices'] }}</div></div>
</div>

<div class="filters-section">
    <form method="GET" action="{{ route('admin.invoices') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Search Invoice / Guest</label>
                <input type="text" name="search" class="filter-input" placeholder="Invoice number, guest name, booking ref..." value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="paid"   {{ request('status')=='paid'   ?'selected':'' }}>Paid</option>
                    <option value="issued" {{ request('status')=='issued' ?'selected':'' }}>Issued</option>
                    <option value="draft"  {{ request('status')=='draft'  ?'selected':'' }}>Draft</option>
                    <option value="void"   {{ request('status')=='void'   ?'selected':'' }}>Void</option>
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

<div class="invoices-table-wrapper">
    @if($invoices->count() > 0)
    <table class="invoices-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Guest</th>
                <th>Booking</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $inv)
            <tr onclick="openInvPanel({{ $inv->id }})">
                <td>
                    <div class="inv-num">{{ $inv->invoice_number }}</div>
                    <div class="inv-date">{{ $inv->issued_at ? $inv->issued_at->format('M d, Y') : 'N/A' }}</div>
                </td>
                <td>
                    <div style="font-weight:600;">{{ $inv->order->booking->guest->name }}</div>
                    <div style="font-size:11px;color:#6b7280;">{{ $inv->order->booking->guest->email }}</div>
                </td>
                <td>
                    <div style="font-size:11px;color:#6b7280;">{{ $inv->order->booking->booking_reference }}</div>
                    <div style="font-weight:600;">{{ $inv->order->booking->property->name }}</div>
                    <div style="font-size:11px;color:#6b7280;">{{ $inv->order->booking->roomType->name }}</div>
                </td>
                <td><div class="amount-val">£{{ number_format($inv->order->grand_total_inc_vat,2) }}</div></td>
                <td>
                    @if($inv->status==='paid')    <span class="badge badge-paid">✓ Paid</span>
                    @elseif($inv->status==='issued') <span class="badge badge-issued">📤 Issued</span>
                    @elseif($inv->status==='draft')  <span class="badge badge-draft">📝 Draft</span>
                    @else <span class="badge badge-void">✕ Void</span>
                    @endif
                </td>
                <td onclick="event.stopPropagation()">
                    <button class="btn-view" onclick="openInvPanel({{ $inv->id }})">View</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-wrapper">
        <div class="pagination-info">Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices</div>
        {{ $invoices->links() }}
    </div>
    @else
    <div class="no-results">
        <div style="font-size:56px;margin-bottom:16px;opacity:.3;">🧾</div>
        <div style="font-size:18px;font-weight:600;margin-bottom:8px;">No Invoices Found</div>
        <div style="font-size:14px;color:#9ca3af;">{{ request()->hasAny(['search','status','from_date','to_date']) ? 'Try adjusting your filters' : 'No invoices have been generated yet' }}</div>
    </div>
    @endif
</div>

{{-- Right-side panel --}}
<div class="inv-overlay" id="invOverlay" onclick="closeInvPanel()"></div>
<div class="inv-panel" id="invPanel">
    <div class="inv-panel-header">
        <div>
            <h2>Invoice Details</h2>
            <p id="invPanelNum">Loading...</p>
        </div>
        <button class="panel-close" onclick="closeInvPanel()">✕</button>
    </div>
    <div class="inv-panel-body" id="invPanelBody">
        <div style="text-align:center;padding:60px 0;color:#9ca3af;">
            <div style="font-size:40px;margin-bottom:12px;">⏳</div>
            <div>Loading...</div>
        </div>
    </div>
</div>

{{-- Embed data --}}
<script>
const invData = {
@foreach($invoices as $inv)
{{ $inv->id }}: {
    id: {{ $inv->id }},
    number:    "{{ $inv->invoice_number }}",
    status:    "{{ $inv->status }}",
    issued_at: "{{ $inv->issued_at ? $inv->issued_at->format('M d, Y') : 'N/A' }}",
    due_at:    "{{ $inv->due_at ? $inv->due_at->format('M d, Y') : 'N/A' }}",
    guest: {
        name:  "{{ addslashes($inv->order->booking->guest->name) }}",
        email: "{{ $inv->order->booking->guest->email }}",
        phone: "{{ $inv->order->booking->guest->phone ?? '—' }}",
    },
    booking: {
        reference: "{{ $inv->order->booking->booking_reference }}",
        status:    "{{ $inv->order->booking->status }}",
        property:  "{{ addslashes($inv->order->booking->property->name) }}",
        location:  "{{ addslashes($inv->order->booking->property->location ?? '') }}",
        room:      "{{ addslashes($inv->order->booking->roomType->name) }}",
        check_in:  "{{ \Carbon\Carbon::parse($inv->order->booking->check_in)->format('M d, Y') }}",
        check_out: "{{ \Carbon\Carbon::parse($inv->order->booking->check_out)->format('M d, Y') }}",
        nights:    {{ \Carbon\Carbon::parse($inv->order->booking->check_in)->diffInDays($inv->order->booking->check_out) }},
    },
    order: {
        number:     "{{ $inv->order->order_number }}",
        subtotal:   {{ $inv->order->subtotal_ex_vat ?? 0 }},
        tax_rate:   {{ $inv->order->tax_rate ?? 0 }},
        vat_total:  {{ $inv->order->vat_total ?? 0 }},
        service_fee:{{ $inv->order->service_fee ?? 0 }},
        grand_total:{{ $inv->order->grand_total_inc_vat ?? 0 }},
        currency:   "{{ $inv->order->currency }}",
    },
},
@endforeach
};

function fmt(n){ return '£'+parseFloat(n).toFixed(2); }

function statusBadge(st){
    const m={paid:['badge-paid','✓ Paid'],issued:['badge-issued','📤 Issued'],draft:['badge-draft','📝 Draft'],void:['badge-void','✕ Void'],confirmed:['badge-paid','✓ Confirmed'],cancelled:['badge-void','✕ Cancelled'],checked_in:['badge-paid','✓ Checked In'],checked_out:['badge-draft','↩ Checked Out']};
    const [c,l]=m[st]||['badge-draft',st];
    return `<span class="p-badge ${c}">${l}</span>`;
}

function openInvPanel(id){
    const o = invData[id];
    if(!o) return;
    document.getElementById('invPanelNum').textContent = o.number + ' · ' + o.issued_at;

    const actionsHtml = `
        <div class="p-actions">
            <button class="p-btn p-btn-blue" onclick="window.open('/admin/invoices/${o.id}/pdf','_blank')">Download PDF</button>
            <button class="p-btn p-btn-teal" onclick="emailInv(${o.id})">Email Guest</button>
            ${(o.status==='issued'||o.status==='draft') ? `<button class="p-btn p-btn-green" onclick="markPaid(${o.id})">Mark as Paid</button>` : ''}
            ${o.status!=='void' ? `<button class="p-btn p-btn-red" onclick="voidInv(${o.id})">Void Invoice</button>` : ''}
        </div>`;

    document.getElementById('invPanelBody').innerHTML = `
        <div class="p-section">
            <div class="p-section-title">🧾 Invoice Information</div>
            <div class="p-row"><span class="p-label">Invoice Number</span><span class="p-value">${o.number}</span></div>
            <div class="p-row"><span class="p-label">Status</span><span class="p-value">${statusBadge(o.status)}</span></div>
            <div class="p-row"><span class="p-label">Issued Date</span><span class="p-value">${o.issued_at}</span></div>
            <div class="p-row"><span class="p-label">Due Date</span><span class="p-value">${o.due_at}</span></div>
            <div class="p-row"><span class="p-label">Order Number</span><span class="p-value">${o.order.number}</span></div>
            ${actionsHtml}
        </div>
        <div class="p-section">
            <div class="p-section-title">👤 Guest Information</div>
            <div class="p-row"><span class="p-label">Name</span><span class="p-value">${o.guest.name}</span></div>
            <div class="p-row"><span class="p-label">Email</span><span class="p-value">${o.guest.email}</span></div>
            <div class="p-row"><span class="p-label">Phone</span><span class="p-value">${o.guest.phone}</span></div>
        </div>
        <div class="p-section">
            <div class="p-section-title">🏨 Booking Details</div>
            <div class="p-row"><span class="p-label">Reference</span><span class="p-value">${o.booking.reference}</span></div>
            <div class="p-row"><span class="p-label">Status</span><span class="p-value">${statusBadge(o.booking.status)}</span></div>
            <div class="p-row"><span class="p-label">Property</span><span class="p-value">${o.booking.property}</span></div>
            <div class="p-row"><span class="p-label">Room Type</span><span class="p-value">${o.booking.room}</span></div>
            <div class="p-row"><span class="p-label">Check-in</span><span class="p-value">${o.booking.check_in}</span></div>
            <div class="p-row"><span class="p-label">Check-out</span><span class="p-value">${o.booking.check_out}</span></div>
            <div class="p-row"><span class="p-label">Nights</span><span class="p-value">${o.booking.nights}</span></div>
        </div>
        <div class="p-section">
            <div class="p-section-title">💰 Financial Breakdown</div>
            <div class="p-row"><span class="p-label">Subtotal (ex-VAT)</span><span class="p-value">${fmt(o.order.subtotal)}</span></div>
            <div class="p-row"><span class="p-label">VAT @${o.order.tax_rate}%</span><span class="p-value">${fmt(o.order.vat_total)}</span></div>
            ${o.order.service_fee>0?`<div class="p-row"><span class="p-label">Service Fee</span><span class="p-value">${fmt(o.order.service_fee)}</span></div>`:''}
            <div class="p-total"><span>Grand Total (inc. VAT)</span><span class="p-total-val">${fmt(o.order.grand_total)}</span></div>
        </div>`;

    document.getElementById('invOverlay').classList.add('open');
    document.getElementById('invPanel').classList.add('open');
    document.body.style.overflow='hidden';
}

function closeInvPanel(){
    document.getElementById('invOverlay').classList.remove('open');
    document.getElementById('invPanel').classList.remove('open');
    document.body.style.overflow='';
}

function emailInv(id){
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        if(!confirm('Send this invoice to the guest by email?')) return;
        performEmailInvoice(id);
        return;
    }
    
    Swal.fire({
        title: 'Email Invoice',
        text: 'Send this invoice to the guest by email?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0891b2',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, send email!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            performEmailInvoice(id);
        }
    });
}

function performEmailInvoice(id) {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    const inputTag = document.querySelector('input[name="_token"]');
    const csrfToken = (metaTag && metaTag.getAttribute('content')) 
                   || (inputTag && inputTag.value)
                   || '{{ csrf_token() }}';
    
    fetch('/admin/invoices/' + id + '/email', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        const success = d.success || d.msgtype === 'success';
        const message = d.message || d.msg || (success ? 'Invoice emailed successfully!' : 'Failed to send email');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: success ? 'Sent!' : 'Error',
                text: message,
                icon: success ? 'success' : 'error',
                confirmButtonColor: success ? '#16a34a' : '#dc2626'
            });
        } else {
            showToast(message, success ? 'success' : 'error');
        }
    })
    .catch(function(err) {
        console.error('Email invoice error:', err);
        const errorMessage = 'Failed to send email: ' + err.message;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        } else {
            showToast(errorMessage, 'error');
        }
    });
}

function markPaid(id){
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        if(!confirm('Mark this invoice as paid?')) return;
        performMarkPaid(id);
        return;
    }
    
    Swal.fire({
        title: 'Mark as Paid',
        text: 'Mark this invoice as paid?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, mark as paid!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            performMarkPaid(id);
        }
    });
}

function performMarkPaid(id) {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    const inputTag = document.querySelector('input[name="_token"]');
    const csrfToken = (metaTag && metaTag.getAttribute('content')) 
                   || (inputTag && inputTag.value)
                   || '{{ csrf_token() }}';
    
    fetch('/admin/invoices/' + id + '/mark-paid', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        const success = d.success || d.msgtype === 'success';
        const message = d.message || d.msg || (success ? 'Invoice marked as paid!' : 'Failed to mark as paid');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: success ? 'Success!' : 'Error',
                text: message,
                icon: success ? 'success' : 'error',
                confirmButtonColor: success ? '#16a34a' : '#dc2626'
            }).then(function() {
                if(success) location.reload();
            });
        } else {
            showToast(message, success ? 'success' : 'error');
            if(success) setTimeout(function() { location.reload(); }, 1500);
        }
    })
    .catch(function(err) {
        console.error('Mark paid error:', err);
        const errorMessage = 'Failed to mark as paid: ' + err.message;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        } else {
            showToast(errorMessage, 'error');
        }
    });
}

function voidInv(id){
    // Check if SweetAlert2 is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 not loaded!');
        if(!confirm('Void this invoice? This cannot be undone.')) return;
    } else {
        Swal.fire({
            title: 'Void Invoice',
            text: 'Are you sure you want to void this invoice? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, void it!',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            
            performVoidInvoice(id);
        });
        return;
    }
    
    performVoidInvoice(id);
}

function performVoidInvoice(id) {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    const inputTag = document.querySelector('input[name="_token"]');
    const csrfToken = (metaTag && metaTag.getAttribute('content')) 
                   || (inputTag && inputTag.value)
                   || '{{ csrf_token() }}';
    
    fetch('/admin/invoices/' + id + '/void', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) { 
        const success = d.success || d.msgtype === 'success';
        const message = d.message || d.msg || (success ? 'Invoice voided successfully!' : 'Failed to void invoice');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: success ? 'Success!' : 'Error',
                text: message,
                icon: success ? 'success' : 'error',
                confirmButtonColor: success ? '#16a34a' : '#dc2626'
            }).then(function() {
                if(success) location.reload();
            });
        } else {
            showToast(message, success ? 'success' : 'error'); 
            if(success) setTimeout(function() { location.reload(); }, 1500);
        }
    })
    .catch(function(err) {
        console.error('Void invoice error:', err);
        const errorMessage = 'Failed to void invoice: ' + err.message;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        } else {
            showToast(errorMessage, 'error');
        }
    });
}

document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeInvPanel(); });
</script>
@endsection
