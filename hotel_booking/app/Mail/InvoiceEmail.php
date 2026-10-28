<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'order' => $this->invoice->order,
                'booking' => $this->invoice->order->booking,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // Attach invoice PDF
        if ($this->invoice->pdf_path) {
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
