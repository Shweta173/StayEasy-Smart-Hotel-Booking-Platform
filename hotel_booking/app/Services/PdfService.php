<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    
    public function generateInvoicePdf(Invoice $invoice)
    {
        // Load invoice with relationships
        $invoice->load(['order.booking.property', 'order.booking.roomType', 'order.booking.guest', 'order.items']);

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'booking' => $invoice->order->booking,
            'property' => $invoice->order->booking->property,
            'roomType' => $invoice->order->booking->roomType,
            'guest' => $invoice->order->booking->guest,
            'order' => $invoice->order,
            'items' => $invoice->order->items
        ]);

        // Save PDF to storage
        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::put($filename, $pdf->output());

        // Update invoice with PDF URL
        $invoice->update([
            'pdf_url' => Storage::url($filename)
        ]);

        return $pdf;
    }

    
    public function downloadInvoice(Invoice $invoice)
    {
        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';

        if (Storage::exists($filename)) {
            return Storage::download($filename, $invoice->invoice_number . '.pdf');
        }

        // Generate if doesn't exist
        $pdf = $this->generateInvoicePdf($invoice);
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    
    public function streamInvoice(Invoice $invoice)
    {
        $filename = 'invoices/' . $invoice->invoice_number . '.pdf';

        if (Storage::exists($filename)) {
            return Storage::response($filename);
        }

        // Generate if doesn't exist
        $pdf = $this->generateInvoicePdf($invoice);
        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
