@extends('admin.layout')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Details')

@section('styles')
<style>
    /* Page Container */
    .invoice-container {
        max-width: 900px;
        margin: 20px auto;
        background: white;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        padding: 0;
    }

    /* Action Bar - Outside Invoice */
    .action-bar {
        background: #f8fafc;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .action-bar .left-actions {
        display: flex;
        gap: 10px;
    }

    .action-bar .right-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        color: #334155;
    }

    .btn-action:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .btn-action.primary {
        background: #0071c2;
        color: white;
        border-color: #0071c2;
    }

    .btn-action.primary:hover {
        background: #005a9c;
    }

    .btn-action.success {
        background: #16a34a;
        color: white;
        border-color: #16a34a;
    }

    .btn-action.success:hover {
        background: #15803d;
    }

    /* Invoice Paper */
    .invoice-paper {
        padding: 48px;
        background: white;
    }

    /* Invoice Header */
    .invoice-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 3px solid #0071c2;
    }

    .company-info h1 {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px 0;
    }

    .company-tagline {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    .invoice-details {
        text-align: right;
    }

    .invoice-title {
        font-size: 32px;
        font-weight: 800;
        color: #0071c2;
        margin: 0 0 8px 0;
    }

    .invoice-number {
        font-size: 14px;
        color: #64748b;
        margin: 0 0 4px 0;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }

    .badge-paid { background: #dcfce7; color: #166534; }
    .badge-issued { background: #dbeafe; color: #1e40af; }
    .badge-draft { background: #fef3c7; color: #92400e; }

    /* Bill To / Booking Info Section */
    .info-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 32px;
    }

    .info-block h3 {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 12px 0;
    }

    .info-block p {
        font-size: 13px;
        color: #334155;
        line-height: 1.6;
        margin: 0 0 4px 0;
    }

    .info-block .name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    /* Invoice Dates */
    .dates-row {
        display: flex;
        gap: 40px;
        margin-bottom: 32px;
        font-size: 13px;
    }

    .dates-row .date-item {
        display: flex;
        gap: 8px;
    }

    .dates-row .date-label {
        color: #64748b;
        font-weight: 600;
    }

    .dates-row .date-value {
        color: #0f172a;
        font-weight: 500;
    }

    /* Items Table */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
        font-size: 13px;
    }

    .items-table thead {
        background: #f8fafc;
        border-top: 2px solid #cbd5e1;
        border-bottom: 2px solid #cbd5e1;
    }

    .items-table th {
        padding: 10px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .items-table th:last-child,
    .items-table td:last-child {
        text-align: right;
    }

    .items-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
    }

    .items-table tbody tr:last-child td {
        border-bottom: 2px solid #cbd5e1;
    }

    /* Totals */
    .totals-section {
        margin-left: auto;
        width: 320px;
        margin-top: 16px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        font-size: 13px;
    }

    .total-row.subtotal {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 10px;
        margin-bottom: 8px;
    }

    .total-row.grand {
        background: #f8fafc;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        padding: 14px 16px;
        margin-top: 12px;
        font-size: 16px;
        font-weight: 700;
    }

    .total-label {
        color: #64748b;
        font-weight: 600;
    }

    .total-value {
        color: #0f172a;
        font-weight: 700;
        font-family: 'Courier New', monospace;
    }

    .total-row.grand .total-label {
        color: #0f172a;
        font-size: 14px;
    }

    .total-row.grand .total-value {
        color: #16a34a;
        font-size: 18px;
    }

    /* Payment Info */
    .payment-section {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    .payment-section h3 {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 12px 0;
    }

    .payment-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        font-size: 13px;
    }

    .payment-item {
        display: flex;
        gap: 8px;
    }

    .payment-item .label {
        color: #64748b;
        font-weight: 600;
        min-width: 120px;
    }

    .payment-item .value {
        color: #0f172a;
        font-weight: 500;
    }

    .status-paid {
        color: #16a34a;
        font-weight: 700;
    }

    .status-pending {
        color: #f59e0b;
        font-weight: 700;
    }

    /* Modal */
    #emailModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 0;
        max-width: 480px;
        width: 90%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #0071c2 0%, #003580 100%);
        padding: 28px 32px;
        position: relative;
    }

    .modal-header::before {
        content: '✉';
        position: absolute;
        font-size: 120px;
        right: -20px;
        top: -30px;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .modal-header h2 {
        font-size: 24px;
        font-weight: 800;
        margin: 0;
        color: white;
        position: relative;
        z-index: 1;
    }

    .modal-body {
        padding: 32px;
    }

    .modal-body p {
        font-size: 15px;
        color: #475569;
        margin: 0 0 8px 0;
        line-height: 1.6;
    }

    .email-display {
        background: #f1f5f9;
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
        text-align: center;
    }

    .email-display .label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: block;
    }

    .email-display .email {
        font-size: 17px;
        font-weight: 700;
        color: #0071c2;
        word-break: break-all;
    }

    .invoice-info {
        margin-top: 20px;
        padding: 16px;
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 6px;
    }

    .invoice-info strong {
        color: #92400e;
        display: block;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding: 24px 32px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .modal-btn {
        padding: 12px 28px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .modal-btn-cancel {
        background: white;
        color: #475569;
        border: 2px solid #cbd5e1;
    }

    .modal-btn-cancel:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .modal-btn-confirm {
        background: #16a34a;
        color: white;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .modal-btn-confirm:hover {
        background: #15803d;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.4);
    }

    .modal-btn-confirm:active {
        transform: translateY(0);
    }

    .modal-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Close button */
    .modal-close {
        position: absolute;
        top: 24px;
        right: 24px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 24px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        z-index: 2;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    /* Print Styles */
    @media print {
        .action-bar { display: none; }
        .invoice-paper { padding: 0; }
        body { margin: 0; }
    }
</style>
@endsection

@section('content')
<div class="invoice-container">
    <!-- Action Bar (Outside Invoice Paper) -->
    <div class="action-bar">
        <div class="left-actions">
            <a href="{{ route('admin.invoices') }}" class="btn-action">
                ← Back
            </a>
        </div>
        <div class="right-actions">
            <button onclick="confirmSendEmail()" class="btn-action success">
                ✉ Email Guest
            </button>
            <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" target="_blank" class="btn-action primary">
                📄 Download PDF
            </a>
        </div>
    </div>

    <!-- Invoice Paper -->
    <div class="invoice-paper">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>Hotel Booking</h1>
                <p class="company-tagline">Premium Hotel Reservation System</p>
            </div>
            <div class="invoice-details">
                <div class="invoice-title">INVOICE</div>
                <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                @if($invoice->status === 'paid')
                    <span class="badge badge-paid">✓ PAID</span>
                @elseif($invoice->status === 'issued')
                    <span class="badge badge-issued">ISSUED</span>
                @else
                    <span class="badge badge-draft">DRAFT</span>
                @endif
            </div>
        </div>

        <!-- Dates -->
        <div class="dates-row">
            <div class="date-item">
                <span class="date-label">Invoice Date:</span>
                <span class="date-value">{{ $invoice->issued_at ? $invoice->issued_at->format('F d, Y') : $invoice->created_at->format('F d, Y') }}</span>
            </div>
            @if($invoice->due_at)
            <div class="date-item">
                <span class="date-label">Due Date:</span>
                <span class="date-value">{{ $invoice->due_at->format('F d, Y') }}</span>
            </div>
            @endif
        </div>

        <!-- Bill To & Booking Info -->
        <div class="info-section">
            <div class="info-block">
                <h3>Bill To</h3>
                <p class="name">{{ $invoice->order->booking->guest->name }}</p>
                <p>{{ $invoice->order->booking->guest->email }}</p>
                @if($invoice->order->booking->guest->phone)
                    <p>{{ $invoice->order->booking->guest->phone }}</p>
                @endif
            </div>

            <div class="info-block">
                <h3>Booking Details</h3>
                <p><strong>Ref:</strong> {{ $invoice->order->booking->booking_reference }}</p>
                <p><strong>Property:</strong> {{ $invoice->order->booking->property->name }}</p>
                <p><strong>Room:</strong> {{ $invoice->order->booking->roomType->name }}</p>
                <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_in)->format('M d, Y') }}</p>
                <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_out)->format('M d, Y') }}</p>
                <p><strong>Nights:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_in)->diffInDays(\Carbon\Carbon::parse($invoice->order->booking->check_out)) }} | <strong>Guests:</strong> {{ $invoice->order->booking->num_guests }}</p>
            </div>
        </div>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 120px;">Unit Price</th>
                    <th style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>£{{ number_format($item->unit_price, 2) }}</td>
                    <td>£{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row subtotal">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">£{{ number_format($invoice->order->subtotal_ex_vat, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">VAT:</span>
                <span class="total-value">£{{ number_format($invoice->order->vat_total, 2) }}</span>
            </div>
            @if($invoice->order->service_fee > 0)
            <div class="total-row">
                <span class="total-label">Service Fee:</span>
                <span class="total-value">£{{ number_format($invoice->order->service_fee, 2) }}</span>
            </div>
            @endif
            @if($invoice->order->processing_fee > 0)
            <div class="total-row">
                <span class="total-label">Processing Fee:</span>
                <span class="total-value">£{{ number_format($invoice->order->processing_fee, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">£{{ number_format($invoice->order->grand_total_inc_vat, 2) }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-section">
            <h3>Payment Information</h3>
            <div class="payment-details">
                <div class="payment-item">
                    <span class="label">Order Number:</span>
                    <span class="value">{{ $invoice->order->order_number }}</span>
                </div>
                <div class="payment-item">
                    <span class="label">Payment Method:</span>
                    <span class="value">{{ ucfirst($invoice->order->payment_method) }}</span>
                </div>
                <div class="payment-item">
                    <span class="label">Payment Status:</span>
                    <span class="value {{ $invoice->order->status === 'paid' ? 'status-paid' : 'status-pending' }}">
                        {{ ucfirst($invoice->order->status) }}
                    </span>
                </div>
                @if($invoice->order->stripe_payment_intent_id)
                <div class="payment-item">
                    <span class="label">Transaction ID:</span>
                    <span class="value" style="font-size: 11px; font-family: monospace;">{{ substr($invoice->order->stripe_payment_intent_id, 0, 30) }}...</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Email Confirmation Modal -->
<div id="emailModal">
    <div class="modal-content">
        <div class="modal-header">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2>📧 Send Invoice via Email</h2>
        </div>
        <div class="modal-body">
            <p>You are about to send this invoice to the guest via email.</p>
            
            <div class="email-display">
                <span class="label">Recipient Email Address</span>
                <div class="email">{{ $invoice->order->booking->guest->email }}</div>
            </div>

            <div class="invoice-info">
                <strong>📄 Invoice Details</strong>
                <div style="font-size: 13px; color: #78350f; margin-top: 4px;">
                    Invoice Number: <strong>{{ $invoice->invoice_number }}</strong><br>
                    Guest Name: <strong>{{ $invoice->order->booking->guest->name }}</strong><br>
                    Amount: <strong>£{{ number_format($invoice->order->grand_total_inc_vat, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button onclick="closeModal()" class="modal-btn modal-btn-cancel">
                ✕ Cancel
            </button>
            <button onclick="sendEmail()" class="modal-btn modal-btn-confirm">
                ✓ Send Email
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmSendEmail() {
    const modal = document.getElementById('emailModal');
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('emailModal');
    modal.style.display = 'none';
}

function sendEmail() {
    const btn = event.target;
    
    // Disable button and show loading state
    btn.disabled = true;
    btn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid white; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span> Sending...';
    
    // Add keyframe animation for spinner
    if (!document.getElementById('spinnerStyle')) {
        const style = document.createElement('style');
        style.id = 'spinnerStyle';
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }
    
    fetch('{{ route("admin.invoices.email", $invoice->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeModal();
        
        if (data.msgtype === 'success' || data.success) {
            // Show success notification
            showNotification('✓ Invoice sent successfully!', 'success');
        } else {
            showNotification('✕ ' + (data.msg || data.message || 'Failed to send invoice'), 'error');
        }
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = '✓ Send Email';
    })
    .catch(error => {
        closeModal();
        console.error('Error:', error);
        showNotification('✕ An error occurred. Please try again.', 'error');
        
        // Reset button
        btn.disabled = false;
        btn.innerHTML = '✓ Send Email';
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 24px;
        right: 24px;
        background: ${type === 'success' ? '#16a34a' : '#dc2626'};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s;
    `;
    notification.textContent = message;
    
    // Add animations
    if (!document.getElementById('notificationStyle')) {
        const style = document.createElement('style');
        style.id = 'notificationStyle';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => notification.remove(), 3000);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Close modal when clicking outside
document.getElementById('emailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
