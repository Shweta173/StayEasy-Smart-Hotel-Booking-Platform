<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmailService
{
    
    public function sendBookingConfirmationOnce(Booking $booking, Invoice $invoice): bool
    {
        $cacheKey = 'email_sent_booking_confirmation_' . $booking->id;
        
        // Check if email was already sent
        if (Cache::has($cacheKey)) {
            Log::info('Booking confirmation email already sent (skipped)', [
                'booking_id' => $booking->id,
                'cache_key' => $cacheKey,
            ]);
            return true; // Already sent, no error
        }

        try {
            $this->sendBookingConfirmation($booking, $invoice);
            
            // Mark as sent (cache for 7 days)
            Cache::put($cacheKey, true, now()->addDays(7));
            
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

   
    private function sendBookingConfirmation(Booking $booking, Invoice $invoice)
    {
        $booking->load(['property', 'roomType', 'guest', 'order']);
        
        $emailData = [
            'booking' => $booking,
            'invoice' => $invoice,
            'guest' => $booking->guest,
            'property' => $booking->property,
            'roomType' => $booking->roomType,
            'order' => $booking->order,
        ];

        Mail::send('emails.booking-confirmation', $emailData, function ($message) use ($booking) {
            $message->to($booking->guest->email, $booking->guest->name)
                    ->subject('Booking Confirmation - ' . $booking->booking_reference . ' - ' . $booking->property->name);
        });

        Log::info('Booking confirmation email sent successfully', [
            'booking_id' => $booking->id,
            'guest_email' => $booking->guest->email,
        ]);
    }

    
    public function sendCancellationEmail(Booking $booking)
    {
        try {
            $booking->load(['property', 'roomType', 'guest']);

            $emailData = [
                'booking' => $booking,
                'guest' => $booking->guest,
                'property' => $booking->property,
                'roomType' => $booking->roomType,
            ];

            Mail::send('emails.booking-cancellation', $emailData, function ($message) use ($booking) {
                $message->to($booking->guest->email, $booking->guest->name)
                        ->subject('Booking Cancelled - ' . $booking->booking_reference);
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send cancellation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
