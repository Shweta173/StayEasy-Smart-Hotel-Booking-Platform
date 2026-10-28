<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .invoice-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
            font-weight: bold;
        }
        .status-paid {
            background: #10b981;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📄 Invoice</h1>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="content">
        <div class="invoice-card">
            <div style="text-align: center;">
                @if($invoice->status === 'paid')
                    <span class="status-badge status-paid">✓ PAID</span>
                @endif
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <p style="color: #666;">Invoice Number</p>
            </div>

            <div class="info-row">
                <span class="info-label">Issue Date:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($invoice->issued_at)->format('F j, Y') }}</span>
            </div>

            @if($invoice->due_at)
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($invoice->due_at)->format('F j, Y') }}</span>
            </div>
            @endif

            @if($booking)
            <div class="info-row">
                <span class="info-label">Booking Reference:</span>
                <span class="info-value">{{ $booking->booking_reference }}</span>
            </div>
            @endif

            <div style="margin: 20px 0; padding: 20px; background: #f0f4ff; border-radius: 6px;">
                <h3 style="margin-top: 0; color: #667eea;">Order Summary</h3>
                
                <div class="info-row">
                    <span class="info-label">Subtotal:</span>
                    <span class="info-value">₹{{ number_format($order->subtotal_ex_vat, 2) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Taxes & Fees:</span>
                    <span class="info-value">₹{{ number_format($order->tax_total, 2) }}</span>
                </div>
                
                <div class="info-row" style="border-top: 2px solid #667eea; margin-top: 10px; padding-top: 10px; font-size: 18px;">
                    <span class="info-label">Total:</span>
                    <span class="info-value" style="font-weight: bold; color: #667eea;">₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <p>Your invoice PDF is attached to this email.</p>
                <a href="{{ config('app.frontend_url') }}/invoice/{{ $invoice->id }}" class="button">
                    View Invoice Online
                </a>
                <a href="{{ config('app.frontend_url') }}/my-bookings" class="button">
                    View My Bookings
                </a>
            </div>
        </div>

        <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2196F3;">
            <strong>📧 Questions?</strong>
            <p style="margin: 10px 0;">
                If you have any questions about this invoice, please contact us at 
                <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
            </p>
        </div>
    </div>

    <div class="footer">
        <p><strong>{{ config('app.name') }}</strong></p>
        <p>This is an automated email. Please do not reply to this message.</p>
        <p style="color: #999; font-size: 12px;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
