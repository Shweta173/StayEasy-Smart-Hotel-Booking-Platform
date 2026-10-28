@extends('admin.layout')

@section('title', 'Payments & Transactions')
@section('page-title', 'Payments & Transactions')

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
        border-left: 4px solid #16a34a;
    }

    .stat-card.stat-refunds { border-left-color: #dc2626; }
    .stat-card.stat-pending { border-left-color: #f59e0b; }
    .stat-card.stat-count { border-left-color: #0071c2; }

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

    .stat-value.refunds {
        color: #dc2626;
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
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
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

    .payments-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .payments-table {
        width: 100%;
        border-collapse: collapse;
    }

    .payments-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .payments-table th {
        padding: 12px 14px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .payments-table td {
        padding: 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        color: #262626;
        vertical-align: middle;
    }

    .payments-table tbody tr {
        transition: all 0.2s ease;
    }

    .payments-table tbody tr:hover {
        background: #f9fafb;
    }

    .payment-id {
        font-family: 'Courier New', monospace;
        font-size: 11px;
        color: #6b7280;
        background: #f3f4f6;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-bottom: 4px;
    }

    .payment-date {
        font-size: 12px;
        color: #9ca3af;
    }

    .order-number {
        font-weight: 700;
        color: #0071c2;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .booking-ref {
        font-size: 11px;
        color: #6b7280;
    }

    .guest-name {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .guest-email {
        font-size: 11px;
        color: #6b7280;
    }

    .amount-value {
        font-size: 16px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }

    .amount-value.charge {
        color: #16a34a;
    }

    .amount-value.refund {
        color: #dc2626;
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

    .badge-succeeded { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fef3c7; color: #a16207; }
    .badge-failed { background: #fee2e2; color: #991b1b; }

    .badge-charge { background: #dbeafe; color: #1e40af; }
    .badge-refund { background: #fee2e2; color: #991b1b; }

    .btn-refund {
        padding: 6px 14px;
        background: #f59e0b;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-refund:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-refund:disabled {
        background: #d1d5db;
        cursor: not-allowed;
        transform: none;
    }

    .btn-refund-pending {
        padding: 6px 14px;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        animation: pulse-red 2s ease-in-out infinite;
    }

    .btn-refund-pending:hover {
        background: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        animation: none;
    }

    @keyframes pulse-red {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
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

    /* Refund Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.2s ease;
    }

    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-header {
        font-size: 20px;
        font-weight: 700;
        color: #262626;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-header .icon {
        font-size: 28px;
    }

    .modal-body {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .refund-details {
        background: #fef3c7;
        border: 2px solid #fbbf24;
        border-radius: 8px;
        padding: 16px;
        margin: 16px 0;
    }

    .refund-detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
    }

    .refund-detail-label {
        font-weight: 600;
        color: #262626;
    }

    .refund-detail-value {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #dc2626;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.2s ease;
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #0071c2;
        box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-modal {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-confirm {
        background: #dc2626;
        color: white;
    }

    .btn-confirm:hover {
        background: #b91c1c;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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
        <div class="stat-label">Total Charged</div>
        <div class="stat-value revenue">£{{ number_format($stats['total_charged'], 2) }}</div>
    </div>
    <div class="stat-card stat-refunds">
        <div class="stat-label">Total Refunded</div>
        <div class="stat-value refunds">£{{ number_format($stats['total_refunded'], 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Net Revenue</div>
        <div class="stat-value revenue">£{{ number_format($stats['net_revenue'], 2) }}</div>
    </div>
    <div class="stat-card stat-count">
        <div class="stat-label">Total Transactions</div>
        <div class="stat-value">{{ $stats['total_transactions'] }}</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="{{ route('admin.payments') }}" id="filterForm">
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    class="filter-input" 
                    placeholder="Stripe ID, order number, guest name..."
                    value="{{ request('search') }}"
                >
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Type</label>
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="charge" {{ request('type') == 'charge' ? 'selected' : '' }}>Charges</option>
                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refunds</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="succeeded" {{ request('status') == 'succeeded' ? 'selected' : '' }}>Succeeded</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
            
            <div class="filter-group" style="display: flex; align-items: end;">
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Payments Table -->
<div class="payments-table-wrapper">
    @if($payments->count() > 0)
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Payment ID & Date</th>
                    <th>Order & Booking</th>
                    <th>Guest</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>
                        <div class="payment-id">{{ $payment->stripe_id }}</div>
                        <div class="payment-date">{{ $payment->created_at->format('M d, Y · g:i A') }}</div>
                    </td>
                    <td>
                        @if($payment->order && $payment->order->booking)
                            <div class="order-number">{{ $payment->order->order_number }}</div>
                            <div class="booking-ref">Booking: {{ $payment->order->booking->booking_reference }}</div>
                        @else
                            <span style="color: #9ca3af;">No booking</span>
                        @endif
                    </td>
                    <td>
                        @if($payment->order && $payment->order->booking)
                            <div class="guest-name">{{ $payment->order->booking->guest->name }}</div>
                            <div class="guest-email">{{ $payment->order->booking->guest->email }}</div>
                        @else
                            <span style="color: #9ca3af;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <div class="amount-value {{ $payment->type }}">
                            @if($payment->type === 'refund')-@endif£{{ number_format($payment->amount, 2) }}
                        </div>
                    </td>
                    <td>
                        @if($payment->type === 'charge')
                            <span class="badge badge-charge">💳 Charge</span>
                        @else
                            <span class="badge badge-refund">↩️ Refund</span>
                        @endif
                    </td>
                    <td>
                        @php
                            // Check if payment succeeded OR order is paid (payment done)
                            // OR if booking was created (which means payment succeeded)
                            $hasBooking = $payment->order && $payment->order->booking_id;
                            $isPaid = $payment->order && $payment->order->status === 'paid';
                            $isSucceeded = $payment->status === 'succeeded' || $isPaid || $hasBooking;
                        @endphp
                        
                        @if($isSucceeded)
                            <span class="badge badge-succeeded">✓ SUCCEEDED</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge badge-pending">⏳ PENDING</span>
                        @else
                            <span class="badge badge-failed">✕ FAILED</span>
                        @endif
                    </td>
                    <td>
                        @php
                            // Use same logic as status display
                            $hasBooking = $payment->order && $payment->order->booking_id;
                            $isPaid = $payment->order && $payment->order->status === 'paid';
                            $isSucceeded = $payment->status === 'succeeded' || $isPaid || $hasBooking;
                        @endphp
                        
                        @if($payment->type === 'charge' && $isSucceeded)
                            @php
                                $refunded = $payment->order->payments()->where('type', 'refund')->where('status', 'succeeded')->sum('amount');
                                $subtotal = $payment->order->subtotal_ex_vat ?? $payment->amount;
                                $taxAmount = $payment->order->tax_amount ?? 0;
                                $serviceFee = $payment->order->service_fee ?? 0;
                                $nonRefundable = $taxAmount + $serviceFee;
                                $maxRefundable = max(0, $subtotal - $refunded);
                                $canRefund = $refunded < $subtotal;
                                
                                // Check if booking was cancelled by customer
                                $booking = $payment->order->booking ?? null;
                                $isCancelledByCustomer = $booking && $booking->status === 'cancelled';
                            @endphp
                            @if($canRefund)
                                @if($isCancelledByCustomer)
                                    {{-- Customer cancelled - urgent RED button --}}
                                    <button 
                                        onclick="showRefundModal({{ $payment->id }}, '{{ $payment->stripe_id }}', {{ $payment->amount }}, {{ $refunded }}, {{ $subtotal }}, {{ $taxAmount }}, {{ $serviceFee }})"
                                        class="btn-refund-pending"
                                        title="Customer cancelled - refund required"
                                    >
                                        ⚠️ Refund Pending
                                    </button>
                                @else
                                    {{-- Normal admin refund - YELLOW/ORANGE button --}}
                                    <button 
                                        onclick="showRefundModal({{ $payment->id }}, '{{ $payment->stripe_id }}', {{ $payment->amount }}, {{ $refunded }}, {{ $subtotal }}, {{ $taxAmount }}, {{ $serviceFee }})"
                                        class="btn-refund"
                                    >
                                        ↩️ Issue Refund
                                    </button>
                                @endif
                            @else
                                <span style="color: #9ca3af; font-size: 11px;">Fully Refunded</span>
                            @endif
                        @else
                            <span style="color: #9ca3af; font-size: 11px;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} transactions
            </div>
            {{ $payments->links() }}
        </div>
    @else
        <div class="no-results">
            <div class="no-results-icon">💳</div>
            <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">No Transactions Found</div>
            <div style="font-size: 14px; color: #9ca3af;">
                @if(request()->hasAny(['search', 'type', 'status', 'from_date']))
                    Try adjusting your filters
                @else
                    No payments have been processed yet
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Refund Modal -->
<div id="refundModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="icon">↩️</span>
            <span>Issue Refund</span>
        </div>
        <div class="modal-body">
            <p>You are about to process a refund for this payment. This action will:</p>
            <ul style="margin: 12px 0; padding-left: 24px; line-height: 1.8;">
                <li>Process refund through Stripe</li>
                <li>Update order status to "refunded"</li>
                <li>Void the associated invoice</li>
                <li>Cannot be undone</li>
            </ul>

            <div class="refund-details">
                <div class="refund-detail-row">
                    <span class="refund-detail-label">Total Charged:</span>
                    <span id="refundOriginalAmount" class="refund-detail-value"></span>
                </div>
                <div class="refund-detail-row" style="color:#dc2626;">
                    <span class="refund-detail-label">Tax (non-refundable):</span>
                    <span id="refundTaxAmount" class="refund-detail-value"></span>
                </div>
                <div class="refund-detail-row" style="color:#dc2626;">
                    <span class="refund-detail-label">Service Fee (non-refundable):</span>
                    <span id="refundServiceFee" class="refund-detail-value"></span>
                </div>
                <div class="refund-detail-row">
                    <span class="refund-detail-label">Already Refunded:</span>
                    <span id="refundAlreadyRefunded" class="refund-detail-value"></span>
                </div>
                <div class="refund-detail-row" style="border-top: 2px solid #fbbf24; padding-top: 12px; margin-top: 8px;">
                    <span class="refund-detail-label">Refundable (room cost only):</span>
                    <span id="refundAvailable" class="refund-detail-value" style="color:#16a34a;"></span>
                </div>
            </div>

            <form id="refundForm">
                <div class="form-group">
                    <label class="form-label">Refund Amount (£)</label>
                    <input 
                        type="number" 
                        id="refundAmount" 
                        class="form-input" 
                        step="0.01" 
                        min="0.01"
                        required
                    >
                    <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                        Enter amount to refund (max: <span id="maxRefundAmount"></span>)
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason (Optional)</label>
                    <textarea 
                        id="refundReason" 
                        class="form-textarea" 
                        placeholder="Enter refund reason for internal records..."
                    ></textarea>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button onclick="closeRefundModal()" class="btn-modal btn-cancel">Cancel</button>
            <button onclick="processRefund()" class="btn-modal btn-confirm" id="confirmRefundBtn">
                Process Refund
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentPaymentId = null;
let maxRefund = 0;

function showRefundModal(paymentId, stripeId, originalAmount, alreadyRefunded, subtotal, taxAmount, serviceFee) {
    currentPaymentId = paymentId;
    // Only the room subtotal is refundable — tax & service fee are kept
    maxRefund = Math.max(0, subtotal - alreadyRefunded);

    document.getElementById('refundOriginalAmount').textContent  = '£' + originalAmount.toFixed(2);
    document.getElementById('refundTaxAmount').textContent       = '-£' + taxAmount.toFixed(2);
    document.getElementById('refundServiceFee').textContent      = '-£' + serviceFee.toFixed(2);
    document.getElementById('refundAlreadyRefunded').textContent = '-£' + alreadyRefunded.toFixed(2);
    document.getElementById('refundAvailable').textContent       = '£' + maxRefund.toFixed(2);
    document.getElementById('maxRefundAmount').textContent       = '£' + maxRefund.toFixed(2);

    document.getElementById('refundAmount').value = maxRefund.toFixed(2);
    document.getElementById('refundAmount').max   = maxRefund;
    document.getElementById('refundReason').value = '';

    document.getElementById('refundModal').classList.add('active');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.remove('active');
    currentPaymentId = null;
}

function processRefund() {
    const amount = parseFloat(document.getElementById('refundAmount').value);
    const reason = document.getElementById('refundReason').value;

    // Validation
    if (!amount || amount <= 0) {
        alert('Please enter a valid refund amount');
        return;
    }

    if (amount > maxRefund) {
        alert('Refund amount cannot exceed £' + maxRefund.toFixed(2));
        return;
    }

    // Disable button
    const btn = document.getElementById('confirmRefundBtn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    // Send refund request
    fetch(`/admin/payments/${currentPaymentId}/refund`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            amount: amount,
            reason: reason
        })
    })
    .then(response => {
        if (!response.ok) {
            // Try to get error message from response
            return response.json().then(data => {
                throw new Error(data.msg || data.message || `HTTP error! status: ${response.status}`);
            }).catch(jsonError => {
                throw new Error(`HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        // Check for success - ApiResponse trait returns msgtype: 'success'
        if (data.msgtype === 'success' || data.success) {
            closeRefundModal();
            showToast(data.msg || data.message || 'Refund processed successfully!', 'success');
            setTimeout(() => location.reload(), 1800);
        } else {
            showToast('Refund failed: ' + (data.msg || data.message || 'Unknown error'), 'error');
            btn.disabled = false;
            btn.textContent = 'Process Refund';
        }
    })
    .catch(error => {
        console.error('Refund error:', error);
        showToast('Error processing refund: ' + error.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Process Refund';
    });
}

// Close modal when clicking outside
document.getElementById('refundModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRefundModal();
    }
});

// Validate refund amount on input
document.getElementById('refundAmount').addEventListener('input', function() {
    const amount = parseFloat(this.value);
    if (amount > maxRefund) {
        this.value = maxRefund.toFixed(2);
    }
});
</script>
@endsection
