<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\EmailService;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-booking {booking_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test booking confirmation email by sending for a specific booking';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService)
    {
        $bookingId = $this->argument('booking_id');

        if (!$bookingId) {
            // Get the latest confirmed booking
            $booking = Booking::with(['guest', 'roomType.property', 'order.invoice'])
                ->where('status', 'confirmed')
                ->latest()
                ->first();

            if (!$booking) {
                $this->error('No confirmed bookings found in the database.');
                $this->info('Complete a test booking first, then try this command.');
                return 1;
            }
        } else {
            $booking = Booking::with(['guest', 'roomType.property', 'order.invoice'])
                ->find($bookingId);

            if (!$booking) {
                $this->error("Booking with ID {$bookingId} not found.");
                return 1;
            }
        }

        $this->info('Testing email for booking: ' . $booking->booking_reference);
        $this->info('Guest: ' . $booking->guest->name . ' (' . $booking->guest->email . ')');
        $this->info('Property: ' . $booking->roomType->property->name);

        try {
            $invoice = $booking->order ? $booking->order->invoice : null;
            
            $emailService->sendBookingConfirmation($booking, $invoice);

            $this->info('✓ Email sent successfully!');
            
            if (config('mail.mailer') === 'log') {
                $this->info('');
                $this->info('MAIL_MAILER=log is set, so email was written to:');
                $this->info('storage/logs/laravel.log');
                $this->info('');
                $this->info('Check the log file to see the email content.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
