<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'date_from' => $request->input('from_date') ?: $request->input('date_from'),
            'date_to' => $request->input('to_date') ?: $request->input('date_to'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 20),
        ];

        $payments = $this->list($filters);
        $stats = $this->getStatistics();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'payments' => $payments->items(),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ], 'Payments retrieved successfully');
        }

        // Return view for web requests
        return view('admin.payments', compact('payments', 'stats'));
    }

    public function refund(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'reason' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $payment = $this->processRefund($id, $request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($payment, 'Refund processed successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.payments')
                ->with('success', 'Refund processed successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    
    protected function list(array $filters = [])
    {
        $query = Payment::with([
            'order' => function($q) {
                $q->select('orders.id', 'orders.order_number', 'orders.booking_id', 'orders.status', 'orders.subtotal_ex_vat', 'orders.tax_amount', 'orders.service_fee');
            },
            'order.booking' => function($q) {
                $q->select('bookings.id', 'bookings.booking_reference', 'bookings.guest_id', 'bookings.status');
            },
            'order.booking.guest' => function($q) {
                $q->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email');
            }
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by type (charge or refund)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Search by payment ID, order number, booking reference, or guest name/email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('stripe_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('order_number', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('order.booking', function($bq) use ($search) {
                      $bq->where('booking_reference', 'LIKE', "%{$search}%");
                  })
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

    
    protected function getStatistics(): array
    {
        // Count charges that are succeeded OR have a booking (which means they completed)
        $totalCharges = Payment::where('type', 'charge')
            ->where(function($q) {
                $q->where('status', 'succeeded')
                  ->orWhereHas('order', function($order) {
                      $order->where('status', 'paid')
                            ->orWhereNotNull('booking_id');
                  });
            })
            ->sum('amount');

        $totalRefunds = Payment::where('type', 'refund')
            ->where('status', 'succeeded')
            ->sum('amount');

        $totalTransactions = Payment::where(function($q) {
            $q->where('status', 'succeeded')
              ->orWhere('status', 'pending');
        })->count();

        return [
            'total_payments' => Payment::where('type', 'charge')->count(),
            'total_refunds' => Payment::where('type', 'refund')->count(),
            'total_charged' => $totalCharges,
            'total_refunded' => $totalRefunds,
            'net_revenue' => $totalCharges - $totalRefunds,
            'total_transactions' => $totalTransactions,
            'pending_refunds' => Payment::where('type', 'refund')
                ->where('status', 'pending')
                ->count(),
        ];
    }

    
    protected function processRefund(int $id, array $data): Payment
    {
        $payment = Payment::findOrFail($id);

        // Validate payment can be refunded
        if ($payment->type !== 'charge') {
            throw new \Exception('Can only refund charge payments');
        }

        if (!in_array($payment->status, ['succeeded', 'pending'])) {
            throw new \Exception('Can only refund succeeded or pending payments');
        }

        // Check if already refunded
        $existingRefund = Payment::where('order_id', $payment->order_id)
            ->where('type', 'refund')
            ->where('status', 'succeeded')
            ->sum('amount');

        if ($existingRefund >= $payment->amount) {
            throw new \Exception('Payment has already been fully refunded');
        }

        $remainingAmount = $payment->amount - $existingRefund;
        if ($data['amount'] > $remainingAmount) {
            throw new \Exception("Refund amount cannot exceed remaining amount of £{$remainingAmount}");
        }

        // Create refund payment record
        $refund = Payment::create([
            'order_id' => $payment->order_id,
            'stripe_id' => 'refund_' . uniqid(),
            'amount' => $data['amount'],
            'currency' => $payment->currency,
            'status' => 'succeeded', // In real implementation, this would be pending until Stripe confirms
            'type' => 'refund',
        ]);

        return $refund;
    }
}
