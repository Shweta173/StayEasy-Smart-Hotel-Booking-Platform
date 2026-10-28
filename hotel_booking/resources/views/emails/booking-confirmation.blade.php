<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #262626;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #003580 0%, #0071c2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .success-badge {
            background-color: #16a34a;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 15px;
            font-size: 14px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #262626;
            margin-bottom: 20px;
        }
        .intro-text {
            font-size: 15px;
            color: #525252;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .booking-card {
            background: #f9fafb;
            border: 2px solid #e7e7e7;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .booking-card h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #003580;
            border-bottom: 2px solid #0071c2;
            padding-bottom: 12px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e7e7e7;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #525252;
            font-size: 14px;
            margin-right: 10px;
        }
        .info-value {
            color: #262626;
            font-size: 14px;
            text-align: right;
        }
        .price-row {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            background: #003580;
            color: white;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            margin-top: 12px;
        }
        .important-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .important-info h3 {
            margin: 0 0 12px 0;
            color: #92400e;
            font-size: 16px;
        }
        .important-info ul {
            margin: 0;
            padding-left: 20px;
            color: #78350f;
        }
        .important-info li {
            margin: 8px 0;
        }
        .cta-button {
            display: inline-block;
            background: #0071c2;
            color: white !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin: 10px 10px 10px 0;
            text-align: center;
        }
        .cta-button:hover {
            background: #003580;
        }
        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e7e7e7;
        }
        .footer p {
            margin: 8px 0;
            font-size: 13px;
            color: #6b6b6b;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            color: #0071c2;
            text-decoration: none;
            margin: 0 10px;
            font-size: 13px;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px 15px;
            }
            .booking-card {
                padding: 16px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-value {
                text-align: left;
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>🎉 Booking Confirmed!</h1>
            <p>Your reservation is confirmed</p>
            <div class="success-badge">✓ CONFIRMED</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Hello {{ $guest->name }},
            </div>

            <div class="intro-text">
                Great news! Your booking at <strong>{{ $property->name }}</strong> has been confirmed. 
                We're excited to host you and ensure you have a wonderful stay.
            </div>

            <!-- Booking Details Card -->
            <div class="booking-card">
                <h2>� Booking Details</h2>
                
                <div class="info-row">
                    <span class="info-label">Confirmation Number:</span>
                    <span class="info-value" style="font-weight: 700; color: #003580;">{{ $booking->booking_reference }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Property:</span>
                    <span class="info-value">{{ $property->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $property->address }}, {{ $property->city }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Room Type:</span>
                    <span class="info-value">{{ $roomType->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Check-in:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('l, F j, Y') }} (after 2:00 PM)</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Check-out:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('l, F j, Y') }} (before 12:00 PM)</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Guests:</span>
                    <span class="info-value">{{ $booking->num_guests }} {{ $booking->num_guests === 1 ? 'guest' : 'guests' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Number of Nights:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }} {{ \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) === 1 ? 'night' : 'nights' }}</span>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="booking-card">
                <h2>� Payment Summary</h2>
                
                <div class="price-row">
                    <div class="info-row">
                        <span class="info-label">Subtotal:</span>
                        <span class="info-value">${{ number_format($order->total_exc_vat, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tax & Fees:</span>
                        <span class="info-value">${{ number_format($order->vat_amount, 2) }}</span>
                    </div>
                </div>

                <div class="total-row">
                    <span>Total Paid:</span>
                    <span>${{ number_format($order->grand_total_inc_vat, 2) }}</span>
                </div>

                <div class="info-row" style="margin-top: 16px;">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value" style="color: #16a34a; font-weight: 600;">✓ PAID</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Invoice Number:</span>
                    <span class="info-value">{{ $invoice->invoice_number }}</span>
                </div>
            </div>

            <!-- Important Information -->
            <div class="important-info">
                <h3>� Important Information</h3>
                <ul>
                    <li><strong>Check-in time:</strong> From 2:00 PM onwards</li>
                    <li><strong>Check-out time:</strong> Before 12:00 PM noon</li>
                    <li><strong>Cancellation:</strong> Free cancellation up to 24 hours before check-in</li>
                    <li><strong>ID Required:</strong> Please bring a valid photo ID and the credit card used for booking</li>
                    <li><strong>Contact Property:</strong> For any special requests, please contact the property directly</li>
                </ul>
            </div>

            <!-- Call to Action Buttons -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/api/public/invoices/{{ $invoice->invoice_number }}/download" class="cta-button">
                    Download Invoice
                </a>
            </div>

            <!-- Contact Info -->
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-top: 30px;">
                <p style="margin: 0 0 12px 0; font-weight: 600; color: #262626;">Need Help?</p>
                <p style="margin: 0; font-size: 14px; color: #525252;">
                    📞 Call us: +1 (555) 123-4567<br>
                    📧 Email: support@hotelbooking.com<br>
                    🕐 Available 24/7 for assistance
                </p>
            </div>

            <div style="margin-top: 30px; font-size: 14px; color: #525252;">
                <p>We look forward to welcoming you!</p>
                <p style="margin-top: 20px;">
                    Best regards,<br>
                    <strong>{{ $property->name }} Team</strong>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Hotel Booking Platform</strong></p>
            <p>Your trusted partner for hotel reservations worldwide</p>
            
            <div class="social-links">
                <a href="#">Facebook</a> | 
                <a href="#">Twitter</a> | 
                <a href="#">Instagram</a>
            </div>

            <p style="margin-top: 20px; font-size: 12px;">
                This email was sent to {{ $guest->email }}<br>
                Booking Reference: {{ $booking->booking_reference }}<br>
                Booking Date: {{ $booking->created_at->format('F j, Y') }}
            </p>

            <p style="font-size: 11px; color: #9ca3af; margin-top: 20px;">
                © {{ date('Y') }} Hotel Booking Platform. All rights reserved.<br>
                Please do not reply to this email. For inquiries, contact support@hotelbooking.com
            </p>
        </div>
    </div>
</body>
</html>
