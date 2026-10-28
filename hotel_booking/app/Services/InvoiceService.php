<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'date_from' => $request->input('from_date') ?: $request->input('date_from'),
            'date_to' => $request->input('to_date') ?: $request->input('date_to'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 20),
        ];

        $invoices = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'invoices' => $invoices->items(),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ],
            ], 'Invoices retrieved successfully');
        }

        // Return view for web requests
        $stats = $this->getInvoiceStatistics();
        return view('admin.invoices', compact('invoices', 'stats'));
    }

    
    public function show(Request $request, $id)
    {
        $invoice = Invoice::with([
            'order.booking.property',
            'order.booking.roomType',
            'order.booking.guest',
            'order.orderItems',
            'order.payments'
        ])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($invoice, 'Invoice retrieved successfully');
        }

        // Return view for web requests
        return view('admin.invoice-detail', compact('invoice'));
    }

    public function sendEmail(Request $request, $id)
    {
        try {
            $invoice = Invoice::with([
                'order.booking.property',
                'order.booking.roomType',
                'order.booking.guest',
                'order.orderItems'
            ])->findOrFail($id);

            // Send email
            $this->sendInvoiceEmail($invoice);

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse(null, 'Invoice email sent successfully to ' . $invoice->order->booking->guest->email);
            }

            return redirect()->back()->with('success', 'Invoice email sent successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse('Failed to send invoice email: ' . $e->getMessage(), 500);
            }

            return redirect()->back()->with('error', 'Failed to send invoice email: ' . $e->getMessage());
        }
    }

    protected function list(array $filters = [])
    {
        $query = Invoice::with([
            'order.booking' => function($q) {
                $q->select('bookings.id', 'bookings.booking_reference', 'bookings.room_type_id', 'bookings.guest_id', 'bookings.check_in', 'bookings.check_out');
            },
            'order.booking.roomType' => function($q) {
                $q->select('room_types.id', 'room_types.name', 'room_types.property_id');
            },
            'order.booking.roomType.property' => function($q) {
                $q->select('properties.id', 'properties.name');
            },
            'order.booking.guest' => function($q) {
                $q->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email');
            }
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('invoice_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('invoice_date', '<=', $filters['date_to']);
        }

        // Search by invoice number or guest email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('order.booking.guest', function($gq) use ($search) {
                      $gq->where('email', 'LIKE', "%{$search}%")
                        ->orWhere('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    
    protected function getInvoiceStatistics(): array
    {
        $paidAmount = Invoice::where('invoices.status', 'paid')
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->sum('orders.grand_total_inc_vat');
            
        $outstandingAmount = Invoice::whereIn('invoices.status', ['issued', 'overdue'])
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->sum('orders.grand_total_inc_vat');
        
        $issuedCount = Invoice::where('status', 'issued')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        
        return [
            'total' => Invoice::count(),
            'total_invoices' => Invoice::count(),
            'total_invoiced' => $paidAmount + $outstandingAmount,
            'issued' => $issuedCount,
            'issued_invoices' => $issuedCount, // Alias for view
            'paid' => $paidCount,
            'paid_invoices' => $paidCount, // Alias for view
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'cancelled' => Invoice::where('status', 'cancelled')->count(),
            'voided' => Invoice::where('status', 'voided')->count(),
            'total_amount' => $paidAmount + $outstandingAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
        ];
    }

    
    public function generateInvoice(Order $order): Invoice
    {
        // Check if invoice already exists
        if ($order->invoice) {
            return $order->invoice;
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'status' => 'paid',
            'issued_at' => Carbon::now(),
            'due_at' => Carbon::now(), // Already paid
            'pdf_url' => null, // Will be generated separately
        ]);

        return $invoice;
    }

    
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        
        return sprintf('INV-%s-%06d', $year, $count);
    }

    
    public function voidInvoice(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'void']);
        return $invoice;
    }

    /**
     * Void an invoice by ID (wrapper for controller)
     */
    public function void(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $result = $this->voidInvoice($invoice);

            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($result, 'Invoice voided successfully');
            }

            return redirect()->route('admin.invoices')
                ->with('success', 'Invoice voided successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    
    public function getInvoiceData(Invoice $invoice): array
    {
        $order = $invoice->order;
        $booking = $order->booking;

        return [
            'invoice' => [
                'number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'issued_at' => $invoice->issued_at->format('F d, Y'),
                'due_at' => $invoice->due_at->format('F d, Y'),
            ],
            'booking' => [
                'reference' => $booking->booking_reference,
                'property' => $booking->roomType->property->name,
                'room_type' => $booking->roomType->name,
                'check_in' => $booking->check_in->format('M d, Y'),
                'check_out' => $booking->check_out->format('M d, Y'),
                'nights' => $booking->num_nights,
            ],
            'guest' => [
                'name' => $booking->guest->full_name,
                'email' => $booking->guest->email,
                'phone' => $booking->guest->phone,
            ],
            'order' => [
                'items' => $order->items->map(function ($item) {
                    return [
                        'description' => $item->description,
                        'qty' => $item->qty,
                        'unit_price' => number_format($item->unit_price, 2),
                        'line_total' => number_format($item->line_total, 2),
                        'type' => $item->item_type,
                    ];
                }),
                'subtotal' => number_format($order->subtotal_ex_vat, 2),
                'tax_total' => number_format($order->tax_total, 2),
                'grand_total' => number_format($order->grand_total_inc_vat, 2),
                'currency' => $order->currency,
                'status' => $order->status,
            ],
            'payments' => $order->payments->where('type', 'charge')->map(function ($payment) {
                return [
                    'amount' => number_format($payment->amount, 2),
                    'status' => $payment->status,
                    'created_at' => $payment->created_at->format('M d, Y H:i'),
                ];
            }),
        ];
    }

    
    protected function sendInvoiceEmail(Invoice $invoice): void
    {
        $guest = $invoice->order->booking->guest;
        $booking = $invoice->order->booking;

        \Mail::send('emails.invoice', [
            'invoice' => $invoice,
            'guest' => $guest,
            'booking' => $booking,
            'order' => $invoice->order,
        ], function ($message) use ($guest, $invoice) {
            $message->to($guest->email, $guest->name)
                    ->subject('Invoice ' . $invoice->invoice_number . ' - Hotel Booking Platform');
        });
    }

    /**
     * Generate and download PDF for an invoice
     */
    public function downloadPdf($id)
    {
        $invoice = Invoice::with([
            'order.booking.property',
            'order.booking.roomType',
            'order.booking.guest',
            'order.orderItems'
        ])->findOrFail($id);

        $booking = $invoice->order->booking;
        $guest = $booking->guest;
        $property = $booking->roomType->property;
        $roomType = $booking->roomType;
        $order = $invoice->order;
        $items = $order->orderItems;

        // Load PDF view
        $pdf = \PDF::loadView('invoices.pdf', compact('invoice', 'booking', 'guest', 'property', 'roomType', 'order', 'items'));

        // Download PDF
        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }
}
