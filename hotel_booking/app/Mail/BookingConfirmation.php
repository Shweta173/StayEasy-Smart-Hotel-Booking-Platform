<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, Invoice $invoice = null)
    {
        $this->booking = $booking;
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - ' . $this->booking->booking_reference,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'invoice' => $this->invoice,
                'property' => $this->booking->roomType->property,
                'roomType' => $this->booking->roomType,
                'guest' => $this->booking->guest,
                'order' => $this->booking->order,
            ],
        );
    }

   
    public function attachments(): array
    {
        $attachments = [];

        // Attach invoice PDF if available
        if ($this->invoice && $this->invoice->pdf_path) {
            $pdfPath = storage_path('app/' . $this->invoice->pdf_path);
            
            if (file_exists($pdfPath)) {
                $attachments[] = Attachment::fromPath($pdfPath)
                    ->as('Invoice-' . $this->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
