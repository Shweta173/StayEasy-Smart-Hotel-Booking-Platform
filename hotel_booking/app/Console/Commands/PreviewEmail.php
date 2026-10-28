<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\View;

class PreviewEmail extends Command
{
    protected $signature = 'booking:preview-email {booking_reference}';
    protected $description = 'Generate email HTML preview for a booking';

    public function handle()
    {
        $reference = $this->argument('booking_reference');
        
        $this->info('Generating email preview for: ' . $reference);

        $booking = Booking::where('booking_reference', $reference)
            ->with(['property', 'roomType', 'guest', 'order', 'order.invoice'])
            ->first();

        if (!$booking) {
            $this->error('✗ Booking not found: ' . $reference);
            return 1;
        }

        if (!$booking->order || !$booking->order->invoice) {
            $this->error('✗ Booking has no order or invoice');
            return 1;
        }

        $emailData = [
            'booking' => $booking,
            'invoice' => $booking->order->invoice,
            'guest' => $booking->guest,
            'property' => $booking->property,
            'roomType' => $booking->roomType,
            'order' => $booking->order,
        ];

        $html = View::make('emails.booking-confirmation', $emailData)->render();
        
        $filename = storage_path('app/email-preview-' . $reference . '.html');
        file_put_contents($filename, $html);

        $this->info('✓ Email preview generated!');
        $this->info('File saved to: ' . $filename);
        $this->info('');
        $this->info('Open this file in your web browser to see the email.');
        
        // Also try to open it automatically
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('start "" "' . $filename . '"');
            $this->info('Email preview opened in your browser!');
        }

        return 0;
    }
}
