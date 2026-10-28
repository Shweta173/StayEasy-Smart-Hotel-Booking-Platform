<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class ListBookings extends Command
{
    protected $signature = 'booking:list';
    protected $description = 'List recent bookings';

    public function handle()
    {
        $bookings = Booking::with(['guest', 'property'])
            ->latest()
            ->take(10)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings found.');
            return;
        }

        $this->info('Recent bookings:');
        $this->info('');

        foreach ($bookings as $booking) {
            $this->line(sprintf(
                '%s | %s | %s | %s',
                $booking->booking_reference,
                $booking->status,
                $booking->guest->email,
                $booking->property->name
            ));
        }
    }
}
