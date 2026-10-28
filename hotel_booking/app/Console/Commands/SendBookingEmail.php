<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\EmailService;

class SendBookingEmail extends Command
{
    protected $signature = 'booking:send-email {booking_reference}';
    protected $description = 'Send booking confirmation email for a specific booking';

    public function handle()
    {
        $reference = $this->argument('booking_reference');
        
        $this->info('Looking for booking: ' . $reference);

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

        $this->info('Found booking for: ' . $booking->guest->name);
        $this->info('Email: ' . $booking->guest->email);
        $this->info('Property: ' . $booking->property->name);

        try {
            $emailService = new EmailService();
            $emailService->sendBookingConfirmation($booking, $booking->order->invoice);

            $this->info('✓ Confirmation email sent successfully!');
            $this->info('Check inbox (and spam) at: ' . $booking->guest->email);
            
        } catch (\Exception $e) {
            $this->error('✗ Failed to send email: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
