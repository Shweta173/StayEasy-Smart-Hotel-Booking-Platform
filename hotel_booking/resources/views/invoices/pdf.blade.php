<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 24px;
            max-width: 100%;
            overflow-x: hidden;
            word-wrap: break-word;
        }

        /* Header */
        .header {
            margin-bottom: 24px;
            border-bottom: 3px solid #003580;
            padding-bottom: 16px;
        }
        .header-table { width: 100%; }
        .header-table td { vertical-align: middle; padding: 0; }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #003580;
        }
        .company-sub { color: #666; font-size: 12px; margin-top: 4px; }
        .invoice-title {
            text-align: right;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 3px;
            color: #333;
        }
        .invoice-number-badge {
            text-align: right;
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }

        /* Status */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-paid   { background: #d4edda; color: #155724; }
        .status-unpaid { background: #f8d7da; color: #721c24; }
        .status-void   { background: #e2e3e5; color: #383d41; }

        /* Meta row */
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { vertical-align: top; padding: 4px 0; width: 33%; }
        .meta-label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-value { font-size: 13px; font-weight: 600; color: #222; margin-top: 2px; }

        /* Section title */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #003580;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #dde;
            padding-bottom: 4px;
            margin: 18px 0 10px 0;
        }

        /* Two-column info */
        .parties-table { 
            width: 100%; 
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .parties-table td { 
            vertical-align: top; 
            width: 250px;
            max-width: 250px;
            padding-right: 16px;
        }
        .info-row { 
            margin-bottom: 4px; 
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-label { font-weight: bold; }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        .items-table th {
            background: #003580;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .items-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #e8e8e8;
            font-size: 13px;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }

        /* Totals — use table not flexbox (DomPDF compat) */
        .totals-wrapper { width: 100%; }
        .totals-table {
            float: right;
            width: 280px;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .totals-table td { padding: 5px 8px; font-size: 13px; }
        .totals-table .label-col { color: #555; }
        .totals-table .value-col { text-align: right; font-weight: 500; }
        .totals-table tr.divider td { border-top: 1px solid #ddd; }
        .totals-table tr.grand td {
            border-top: 2px solid #333;
            font-size: 15px;
            font-weight: bold;
            padding-top: 8px;
            color: #003580;
        }
        .clearfix { clear: both; }

        /* Footer */
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #888;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">Hotel Booking Platform</div>
                <div class="company-sub">Professional Hotel Reservation Services</div>
            </td>
            <td>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number-badge">{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Invoice meta ── --}}
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Issue Date</div>
                <div class="meta-value">{{ $invoice->issued_at->format('M d, Y') }}</div>
            </td>
            <td>
                <div class="meta-label">Due Date</div>
                <div class="meta-value">{{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : 'Immediate' }}</div>
            </td>
            <td>
                <div class="meta-label">Status</div>
                <div class="meta-value" style="margin-top:4px;">
                    <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Bill To / Property ── --}}
    <div class="section-title">Details</div>
    <table class="parties-table">
        <tr>
            <td style="width: 50%; max-width: 250px;">
                <div class="info-label" style="margin-bottom:6px;">Bill To</div>
                <div class="info-row">{{ $guest->first_name }} {{ $guest->last_name }}</div>
                <div class="info-row" style="color:#555; font-size: 11px;">{{ $guest->email }}</div>
                <div class="info-row" style="color:#555; font-size: 11px;">{{ $guest->phone }}</div>
            </td>
            <td style="width: 50%; max-width: 250px;">
                <div class="info-label" style="margin-bottom:6px;">Property</div>
                <div class="info-row"><strong>{{ Str::limit($property->name, 40) }}</strong></div>
                @if($property->address)
                    <div class="info-row" style="color:#555; font-size: 10px;">{{ Str::limit($property->address, 50) }}</div>
                @endif
                <div class="info-row" style="color:#555; font-size: 11px;">
                    {{ $property->city }}{{ $property->city && $property->country ? ', ' : '' }}{{ $property->country }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Booking details ── --}}
    <div class="section-title">Booking Details</div>
    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Booking Reference</div>
                <div class="meta-value">{{ $booking->booking_reference }}</div>
            </td>
            <td>
                <div class="meta-label">Room Type</div>
                <div class="meta-value">{{ $roomType->name }}</div>
            </td>
            <td>
                <div class="meta-label">Guests</div>
                <div class="meta-value">{{ $booking->num_guests }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-label">Check-in</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</div>
            </td>
            <td>
                <div class="meta-label">Check-out</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</div>
            </td>
            <td>
                <div class="meta-label">Nights</div>
                <div class="meta-value">
                    {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Line items ── --}}
    <div class="section-title">Charges</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width:80px;">Qty</th>
                <th class="text-right" style="width:110px;">Unit Price</th>
                <th class="text-right" style="width:110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">{{ $order->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $order->currency }} {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    <div class="totals-wrapper">
        <table class="totals-table">
            <tr>
                <td class="label-col">Subtotal</td>
                <td class="value-col">{{ $order->currency }} {{ number_format($order->subtotal_ex_vat, 2) }}</td>
            </tr>
            @if($order->tax_amount > 0)
            <tr>
                <td class="label-col">Tax</td>
                <td class="value-col">{{ $order->currency }} {{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($order->service_fee > 0)
            <tr>
                <td class="label-col">Service Fee</td>
                <td class="value-col">{{ $order->currency }} {{ number_format($order->service_fee, 2) }}</td>
            </tr>
            @endif
            <tr class="grand">
                <td class="label-col">Grand Total</td>
                <td class="value-col">{{ $order->currency }} {{ number_format($order->grand_total_inc_vat, 2) }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <p><strong>Thank you for choosing Hotel Booking Platform!</strong></p>
        <p>For invoice enquiries contact: support@hotelbooking.com</p>
        <p style="margin-top:10px;">This is a computer-generated invoice and does not require a signature.</p>
    </div>

</body>
</html>
