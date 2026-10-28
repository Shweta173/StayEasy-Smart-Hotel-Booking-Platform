<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\OrderService;
use App\Models\BookingHold;
use App\Models\RoomType;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $bookingService;
    protected $orderService;

    public function __construct(
        BookingService $bookingService,
        OrderService $orderService
    ) {
        $this->bookingService = $bookingService;
        $this->orderService = $orderService;
    }

    /**
     * Frontend Page 5: Checkout - Create hold and order
     * This is called when guest clicks "Book Now" and proceeds to checkout
     */
    public function createHold(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'num_rooms' => 'required|integer|min:1|max:10', // Number of rooms to book
            'num_guests' => 'nullable|integer|min:1', // Number of guests
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'required|string|max:255',
            'guest.email' => 'required|email|max:255',
            'guest.phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);

        try {
            // Validate guest count against room type max occupancy
            $roomType = RoomType::findOrFail($validated['room_type_id']);
            $numGuests = $validated['num_guests'] ?? 1;
            $numRooms = $validated['num_rooms'] ?? 1;
            $guestsPerRoom = ceil($numGuests / $numRooms);

            if ($guestsPerRoom > $roomType->max_occupancy) {
                return response()->json([
                    'success' => false,
                    'message' => "Each room can accommodate a maximum of {$roomType->max_occupancy} guests. With {$numRooms} room(s) and {$numGuests} guest(s), you need {$guestsPerRoom} guests per room.",
                ], 422);
            }

            $result = $this->bookingService->createHoldAndOrder($validated);

            // Log the order tax rate for debugging
            \Log::info('Order Created in createHold', [
                'order_id' => $result['order']->id,
                'tax_rate' => $result['order']->tax_rate,
                'tax_amount' => $result['order']->tax_amount,
                'grand_total' => $result['order']->grand_total_inc_vat,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking hold created. Please complete payment within 15 minutes.',
                'hold' => [
                    'id' => $result['hold']->id,
                    'expires_at' => $result['hold']->expires_at->toIso8601String(),
                    'expires_in_minutes' => $result['hold']->expires_at->diffInMinutes(now()),
                ],
                'order' => [
                    'id' => $result['order']->id,
                    'order_number' => $result['order']->order_number,
                    'subtotal_ex_vat' => (float) $result['order']->subtotal_ex_vat,
                    'tax_rate' => (float) $result['order']->tax_rate,
                    'tax_amount' => (float) $result['order']->tax_amount,
                    'grand_total' => (float) $result['order']->grand_total_inc_vat,
                    'grand_total_inc_vat' => (float) $result['order']->grand_total_inc_vat,
                    'currency' => $result['order']->currency,
                    'items' => $result['order']->items->map(function ($item) {
                        return [
                            'description' => $item->description,
                            'qty' => $item->qty,
                            'unit_price' => number_format($item->unit_price, 2),
                            'line_total' => number_format($item->line_total, 2),
                            'type' => $item->item_type,
                        ];
                    }),
                ],
                'booking_details' => [
                    'room_type' => $result['hold']->roomType->name,
                    'property' => $result['hold']->roomType->property->name,
                    'check_in' => $result['hold']->check_in->format('Y-m-d'),
                    'check_out' => $result['hold']->check_out->format('Y-m-d'),
                    'nights' => $result['hold']->check_in->diffInDays($result['hold']->check_out),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get pricing breakdown (for checkout preview)
     */
    public function getPricing(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'num_rooms' => 'required|integer|min:1|max:10', // Number of rooms
        ]);

        $pricing = $this->orderService->getPricingBreakdown(
            $validated['room_type_id'],
            $validated['check_in'],
            $validated['check_out'],
            $validated['num_rooms'] // Pass number of rooms
        );

        return response()->json([
            'success' => true,
            'pricing' => $pricing,
        ]);
    }

    /**
     * Check hold status (for countdown timer)
     */
    public function checkHoldStatus($holdId)
    {
        $hold = BookingHold::findOrFail($holdId);
        $isExpired = $hold->isExpired();
        $isActive = $hold->isActive();

        return response()->json([
            'success' => true,
            'hold' => [
                'id' => $hold->id,
                'status' => $hold->status,
                'expires_at' => $hold->expires_at->toIso8601String(),
                'expires_in_seconds' => $isActive ? $hold->expires_at->diffInSeconds(now()) : 0,
                'is_expired' => $isExpired,
                'is_active' => $isActive,
            ],
        ]);
    }

    /**
     * Lookup booking by reference (for guests without account)
     */
    public function lookupBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_reference' => 'required|string',
            'email' => 'required|email',
        ]);

        $booking = $this->bookingService->getBookingByReference(
            $validated['booking_reference'],
            $validated['email']
        );

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found or email does not match',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'reference' => $booking->booking_reference,
                'status' => $booking->status,
                'property' => [
                    'name' => $booking->roomType->property->name,
                ],
                'room_type' => [
                    'name' => $booking->roomType->name,
                ],
                'check_in' => $booking->check_in->format('Y-m-d'),
                'check_out' => $booking->check_out->format('Y-m-d'),
                'nights' => $booking->num_nights,
                'guests' => $booking->num_guests,
                'guest' => [
                    'first_name' => $booking->guest->first_name,
                    'last_name' => $booking->guest->last_name,
                    'email' => $booking->guest->email,
                    'phone' => $booking->guest->phone,
                ],
                'order' => [
                    'grand_total' => $booking->order->grand_total_inc_vat,
                    'status' => $booking->order->status,
                    'invoice_number' => $booking->order->invoice ? $booking->order->invoice->invoice_number : null,
                    'items' => $booking->order->items->map(function ($item) {
                        return [
                            'description' => $item->description,
                            'line_total' => number_format($item->line_total, 2),
                        ];
                    }),
                ],
                'invoice' => $booking->order->invoice ? [
                    'invoice_number' => $booking->order->invoice->invoice_number,
                    'status' => $booking->order->invoice->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Get booking by reference (for confirmation page)
     */
    public function getByReference($reference)
    {
        $booking = Booking::where('booking_reference', $reference)
            ->with(['roomType.property', 'guest', 'order.items', 'order.invoice'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Send confirmation email if not already sent
        // Check if email was sent in the last hour (to avoid duplicates on page refresh)
        // Send confirmation email using the service's idempotent method
        if ($booking->order && $booking->order->invoice) {
            try {
                $emailService = app(\App\Services\EmailService::class);
                $emailService->sendBookingConfirmationOnce($booking, $booking->order->invoice);

                Log::info('Confirmation email sent from confirmation page', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'guest_email' => $booking->guest->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send confirmation email from confirmation page', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the API call if email fails
            }
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'reference' => $booking->booking_reference,
                'status' => $booking->status,
                'property' => [
                    'name' => $booking->roomType->property->name,
                ],
                'room_type' => [
                    'name' => $booking->roomType->name,
                ],
                'check_in' => $booking->check_in->format('Y-m-d'),
                'check_out' => $booking->check_out->format('Y-m-d'),
                'nights' => $booking->num_nights,
                'guests' => $booking->num_guests,
                'guest' => [
                    'first_name' => $booking->guest->first_name,
                    'last_name' => $booking->guest->last_name,
                    'email' => $booking->guest->email,
                    'phone' => $booking->guest->phone,
                ],
                'order' => [
                    'grand_total' => $booking->order->grand_total_inc_vat,
                    'status' => $booking->order->status,
                    'invoice_number' => $booking->order->invoice ? $booking->order->invoice->invoice_number : null,
                ],
            ],
        ]);
    }

    /**
     * Cancel a booking (Public endpoint for guests)
     * Allows guests to cancel their own bookings
     */
    public function cancelBooking($id, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'email' => 'required|email', // For verification
        ]);

        try {
            // Find the booking
            $booking = Booking::with(['guest', 'roomType.property', 'order'])
                ->findOrFail($id);

            // Verify the email matches (security check)
            if ($booking->guest->email !== $validated['email']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email does not match booking records',
                ], 403);
            }

            // Check if booking can be cancelled
            if (!in_array($booking->status, ['confirmed', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be cancelled. Status: ' . $booking->status,
                ], 422);
            }

            // Calculate refund amount (full refund if more than 24 hours before check-in)
            $checkInDate = Carbon::parse($booking->check_in);
            $hoursUntilCheckIn = now()->diffInHours($checkInDate, false);

            $refundPercentage = 100; // Default full refund
            $refundReason = 'Full refund - cancelled more than 24 hours before check-in';

            if ($hoursUntilCheckIn < 24 && $hoursUntilCheckIn > 0) {
                $refundPercentage = 50; // 50% refund if less than 24 hours
                $refundReason = '50% refund - cancelled within 24 hours of check-in';
            } elseif ($hoursUntilCheckIn <= 0) {
                $refundPercentage = 0; // No refund if check-in has passed
                $refundReason = 'No refund - check-in date has passed';
            }

            $totalAmount = $booking->order ? $booking->order->grand_total_inc_vat : 0;
            $refundAmount = ($totalAmount * $refundPercentage) / 100;

            // Update booking status
            $booking->status = 'cancelled';
            $booking->cancellation_reason = $validated['reason'];
            $booking->cancelled_by = 'guest';
            $booking->cancelled_at = now();
            $booking->refund_amount = $refundAmount;
            $booking->refund_percentage = $refundPercentage;
            $booking->refund_status = $refundAmount > 0 ? 'pending' : 'not_applicable';
            $booking->save();

            // Log the cancellation
            \Log::info('Booking cancelled by guest', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'guest_email' => $booking->guest->email,
                'reason' => $validated['reason'],
                'refund_amount' => $refundAmount,
                'refund_percentage' => $refundPercentage,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'booking' => [
                    'reference' => $booking->booking_reference,
                    'status' => 'cancelled',
                    'cancelled_at' => $booking->cancelled_at->toIso8601String(),
                ],
                'refund' => [
                    'amount' => number_format($refundAmount, 2),
                    'percentage' => $refundPercentage,
                    'reason' => $refundReason,
                    'status' => $booking->refund_status,
                    'processing_time' => '5-7 business days',
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Booking cancellation failed', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking: ' . $e->getMessage(),
            ], 500);
        }
    }
}

