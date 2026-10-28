<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 60px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 50px;
            border-bottom: 3px solid #0071c2;
            padding-bottom: 20px;
        }

        .company-info h1 {
            color: #0071c2;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .company-info p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .invoice-info {
            text-align: right;
        }

        .invoice-title {
            font-size: 42px;
            font-weight: 700;
            color: #262626;
            margin-bottom: 10px;
        }

        .invoice-number {
            font-size: 18px;
            color: #0071c2;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .invoice-meta {
            font-size: 13px;
            color: #666;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-paid { background: #dcfce7; color: #166534; }
        .status-issued { background: #dbeafe; color: #1e40af; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-void { background: #fee2e2; color: #991b1b; }

        .details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .detail-box h3 {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .detail-box p {
            font-size: 14px;
            color: #262626;
            line-height: 1.8;
            margin-bottom: 4px;
        }

        .detail-box .highlight {
            font-weight: 700;
            color: #0071c2;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .items-table td {
            padding: 14px 12px;
            font-size: 14px;
            color: #262626;
        }

        .items-table .item-description {
            font-weight: 600;
        }

        .items-table .item-type-tax {
            color: #6b7280;
            font-style: italic;
        }

        .totals-section {
            margin-left: auto;
            width: 350px;
            background: #f9fafb;
            padding: 24px;
            border-radius: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .total-row.subtotal {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 12px;
            margin-bottom: 8px;
        }

        .total-row.grand {
            border-top: 3px solid #262626;
            padding-top: 16px;
            margin-top: 12px;
            font-size: 20px;
            font-weight: 700;
        }

        .total-label {
            color: #6b7280;
            font-weight: 500;
        }

        .total-value {
            color: #262626;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .total-row.grand .total-value {
            color: #16a34a;
            font-size: 24px;
        }

        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            line-height: 1.8;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #0071c2;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,113,194,0.3);
        }

        .print-button:hover {
            background: #00509e;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .invoice-container {
                box-shadow: none;
                padding: 40px;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>Hotel Booking Platform</h1>
                <p>
                    Premium Hotel Booking Service<br>
                    Email: billing@hotelbooking.com<br>
                    Phone: +44 (0) 20 1234 5678<br>
                    VAT No: GB123456789
                </p>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="invoice-meta">
                    <strong>Issued:</strong> {{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : 'N/A' }}<br>
                    @if($invoice->due_at)
                        <strong>Due:</strong> {{ $invoice->due_at->format('d M Y') }}<br>
                    @endif
                </div>
                <span class="status-badge status-{{ $invoice->status }}">
                    @if($invoice->status === 'paid')
                        ✓ PAID
                    @elseif($invoice->status === 'issued')
                        📤 ISSUED
                    @elseif($invoice->status === 'draft')
                        📝 DRAFT
                    @else
                        ✕ VOID
                    @endif
                </span>
            </div>
        </div>

        <!-- Customer & Booking Details -->
        <div class="details-section">
            <div class="detail-box">
                <h3>Bill To</h3>
                <p class="highlight">{{ $invoice->order->booking->guest->name }}</p>
                <p>{{ $invoice->order->booking->guest->email }}</p>
                @if($invoice->order->booking->guest->phone)
                    <p>{{ $invoice->order->booking->guest->phone }}</p>
                @endif
            </div>
            <div class="detail-box">
                <h3>Booking Details</h3>
                <p><strong>Booking Ref:</strong> {{ $invoice->order->booking->booking_reference }}</p>
                <p><strong>Property:</strong> {{ $invoice->order->booking->property->name }}</p>
                <p><strong>Room:</strong> {{ $invoice->order->booking->roomType->name }}</p>
                <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_in)->format('d M Y') }}</p>
                <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_out)->format('d M Y') }}</p>
                <p><strong>Nights:</strong> {{ \Carbon\Carbon::parse($invoice->order->booking->check_in)->diffInDays(\Carbon\Carbon::parse($invoice->order->booking->check_out)) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->order->items->where('item_type', 'room_night') as $item)
                <tr>
                    <td class="item-description">{{ $item->description }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>£{{ number_format($item->unit_price, 2) }}</td>
                    <td>£{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
                
                @foreach($invoice->order->items->where('item_type', 'tax') as $item)
                <tr class="item-type-tax">
                    <td colspan="3" style="text-align: right; padding-right: 12px;">{{ $item->description }}</td>
                    <td>£{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
                
                @foreach($invoice->order->items->where('item_type', 'fee') as $item)
                <tr class="item-type-tax">
                    <td colspan="3" style="text-align: right; padding-right: 12px;">{{ $item->description }}</td>
                    <td>£{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row subtotal">
                <span class="total-label">Subtotal (ex-VAT):</span>
                <span class="total-value">£{{ number_format($invoice->order->subtotal_ex_vat, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">VAT @{{ $invoice->order->tax_rate }}%:</span>
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
                <span class="total-label">TOTAL (inc-VAT):</span>
                <span class="total-value">£{{ number_format($invoice->order->grand_total_inc_vat, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Payment Method:</strong> {{ ucfirst($invoice->order->payment_method) }}</p>
            @if($invoice->order->stripe_payment_intent_id)
                <p>Payment ID: {{ $invoice->order->stripe_payment_intent_id }}</p>
            @endif
            <p style="margin-top: 20px;">
                Thank you for your business!<br>
                For any queries, please contact billing@hotelbooking.com
            </p>
        </div>
    </div>
</body>
</html>
