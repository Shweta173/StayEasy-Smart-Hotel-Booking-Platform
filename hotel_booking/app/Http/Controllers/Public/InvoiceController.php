<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Frontend Page 10: Invoice / Receipt
     * Get invoice data for display
     */
    public function show($invoiceNumber)
    {
        try {
            $invoice = Invoice::with([
                'order.booking.roomType.property',
                'order.booking.guest',
                'order.items',
                'order.payments'
            ])->where('invoice_number', $invoiceNumber)->firstOrFail();
            
            $order = $invoice->order;
            $booking = $order->booking;
            $property = $booking->roomType->property;
            $guest = $booking->guest;

            // Calculate nights
            $checkIn = Carbon::parse($booking->check_in);
            $checkOut = Carbon::parse($booking->check_out);
            $nights = $checkIn->diffInDays($checkOut);

            // Prepare invoice items from order items
            $items = $order->items->map(function($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->qty,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->line_total,
                ];
            })->toArray();

            // Prepare payment records
            $payments = $order->payments->where('status', 'succeeded')->map(function($payment) {
                return [
                    'payment_method' => ucfirst($payment->payment_method),
                    'transaction_id' => $payment->stripe_id,
                    'amount' => $payment->amount,
                    'paid_at' => $payment->created_at,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice_number' => $invoice->invoice_number,
                    'issued_date' => $invoice->issued_at,
                    'due_date' => $invoice->due_at,
                    'status' => $invoice->status,
                    'guest' => [
                        'first_name' => $guest->first_name,
                        'last_name' => $guest->last_name,
                        'email' => $guest->email,
                        'phone' => $guest->phone,
                    ],
                    'property' => [
                        'name' => $property->name,
                        'address' => $property->address ?? '',
                        'city' => $property->city ?? '',
                        'state' => $property->state ?? '',
                        'postal_code' => $property->postal_code ?? '',
                        'country' => $property->country ?? 'USA',
                    ],
                    'booking' => [
                        'booking_reference' => $booking->booking_reference,
                        'room_type' => $booking->roomType->name,
                        'check_in_date' => $booking->check_in,
                        'check_out_date' => $booking->check_out,
                        'nights' => $nights,
                        'guests' => $booking->num_guests,
                    ],
                    'items' => $items,
                    'subtotal' => $order->subtotal_ex_vat,
                    'tax_amount' => $order->tax_total,
                    'service_fee' => 0,
                    'total_amount' => $order->grand_total_inc_vat,
                    'payments' => $payments,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }
    }

    /**
     * Download invoice PDF
     */
    public function download($invoiceNumber)
    {
        try {
            $invoice = Invoice::with([
                'order.booking.roomType.property',
                'order.booking.guest',
                'order.items',
                'order.payments'
            ])->where('invoice_number', $invoiceNumber)->firstOrFail();

            $order   = $invoice->order;
            $booking = $order->booking;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', [
                'invoice'  => $invoice,
                'booking'  => $booking,
                'property' => $booking->roomType->property,
                'roomType' => $booking->roomType,
                'guest'    => $booking->guest,
                'order'    => $order,
                'items'    => $order->items,
            ]);

            return $pdf->download('Invoice-' . $invoiceNumber . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }
}
