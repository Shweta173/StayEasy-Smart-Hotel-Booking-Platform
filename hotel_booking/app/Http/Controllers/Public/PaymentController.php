<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\BookingHold;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe PaymentIntent for order
     * Frontend Page 5: Checkout - Step 2
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'hold_id' => 'required|exists:booking_holds,id',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $hold = BookingHold::findOrFail($validated['hold_id']);

            // Verify hold is still active
            if ($hold->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking hold has expired. Please start over.',
                ], 422);
            }

            // Calculate amount in cents (Stripe requires smallest currency unit)
            $amountInCents = (int) ($order->grand_total_inc_vat * 100);

            // Create Stripe PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => strtolower($order->currency),
                'description' => 'Hotel Booking - Order #' . $order->order_number,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'hold_id' => $hold->id,
                    'guest_email' => $order->guest->email,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Update order with payment intent ID
            $order->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            // Link payment intent to hold
            $hold = BookingHold::where('guest_id', $order->guest_id)
                ->where('status', 'pending')
                ->latest()
                ->first();
            
            if ($hold) {
                $hold->update(['payment_intent_id' => $paymentIntent->id]);
            }

            // Create payment record for webhook tracking
            Payment::create([
                'order_id' => $order->id,
                'stripe_id' => $paymentIntent->id,
                'amount' => $order->grand_total_inc_vat,
                'currency' => $order->currency,
                'status' => 'pending',
                'payment_method' => 'card',
                'metadata' => [
                    'hold_id' => $hold ? $hold->id : null,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'payment_intent' => [
                    'id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'amount' => $order->grand_total_inc_vat,
                    'currency' => $order->currency,
                ],
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function checkPaymentStatus($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            $order = Order::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // ── Fallback confirmation (webhook missed / local dev) ─────────────
            // If Stripe confirms payment but our order is still pending,
            // process it here — same logic as the webhook handler.
            if ($paymentIntent->status === 'succeeded' && $order->status === 'pending') {
                try {
                    $bookingService = app(\App\Services\BookingService::class);
                    $orderService   = app(\App\Services\OrderService::class);
                    $invoiceService = app(\App\Services\InvoiceService::class);

                    // Find the hold
                    $hold = BookingHold::where('payment_intent_id', $paymentIntentId)->first();
                    
                    if (!$hold) {
                        \Log::error('Hold not found in polling fallback', ['payment_intent_id' => $paymentIntentId]);
                        
                        // Mark payment and order as failed
                        $payment = Payment::where('stripe_id', $paymentIntentId)->first();
                        if ($payment) {
                            $payment->update(['status' => 'failed']);
                        }
                        $order->update(['status' => 'failed']);
                        
                        return response()->json([
                            'success' => false,
                            'status' => 'failed',
                            'message' => 'Booking hold not found',
                        ]);
                    }

                    // *** CRITICAL: Validate room is still available ***
                    $isAvailable = $bookingService->validateRoomAvailability(
                        $hold->room_type_id,
                        $hold->check_in,
                        $hold->check_out,
                        $hold->id
                    );

                    if (!$isAvailable) {
                        \Log::error('Room no longer available at payment confirmation (polling)', [
                            'hold_id' => $hold->id,
                            'room_type_id' => $hold->room_type_id,
                        ]);

                        // Mark payment as failed
                        $payment = Payment::where('stripe_id', $paymentIntentId)->first();
                        if ($payment) {
                            $payment->update([
                                'status' => 'failed',
                                'metadata' => array_merge($payment->metadata ?? [], [
                                    'failed_at' => now()->toIso8601String(),
                                    'failure_reason' => 'Room no longer available',
                                ]),
                            ]);
                        }

                        // Mark order as failed
                        $order->update(['status' => 'failed']);

                        // Mark hold as expired
                        $hold->update(['status' => 'expired']);

                        // Refund the payment immediately
                        try {
                            $stripeRefund = \Stripe\Refund::create([
                                'payment_intent' => $paymentIntentId,
                                'reason' => 'requested_by_customer',
                            ]);

                            \Log::info('Payment refunded - room unavailable (polling)', [
                                'payment_intent_id' => $paymentIntentId,
                                'refund_id' => $stripeRefund->id,
                            ]);

                            // Create refund payment record
                            if ($payment) {
                                Payment::create([
                                    'order_id' => $order->id,
                                    'stripe_id' => $stripeRefund->id,
                                    'amount' => $payment->amount,
                                    'type' => 'refund',
                                    'status' => 'succeeded',
                                    'metadata' => [
                                        'refund_id' => $stripeRefund->id,
                                        'original_payment_id' => $payment->id,
                                        'reason' => 'Room unavailable at confirmation',
                                    ],
                                ]);
                            }

                            // Update order to refunded status
                            $order->update(['status' => 'refunded']);

                        } catch (\Exception $e) {
                            \Log::error('Failed to refund payment for unavailable room (polling)', [
                                'payment_intent_id' => $paymentIntentId,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        return response()->json([
                            'success' => false,
                            'status' => 'failed',
                            'message' => 'Room is no longer available. Your payment has been refunded.',
                        ]);
                    }

                    // Mark payment record as succeeded
                    $payment = Payment::where('stripe_id', $paymentIntentId)->first();
                    if ($payment && $payment->status !== 'succeeded') {
                        $payment->update([
                            'status'   => 'succeeded',
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'processed_at'    => now()->toIso8601String(),
                                'confirmed_via'   => 'polling_fallback',
                                'amount_received' => $paymentIntent->amount_received / 100,
                            ]),
                        ]);
                    }

                    // Mark order paid
                    $orderService->markOrderAsPaid($order);
                    $order->refresh();

                    // Confirm booking from hold
                    if (!$order->booking_id) {
                        $booking = $bookingService->confirmBooking($hold, $order);
                        $invoiceService->generateInvoice($order);

                        // Send confirmation email
                        try {
                            $emailService = app(\App\Services\EmailService::class);
                            $invoice = $order->fresh()->invoice;
                            if ($invoice) {
                                $emailService->sendBookingConfirmation($booking, $invoice);
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Email failed in polling fallback', ['error' => $e->getMessage()]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Polling fallback confirmation failed', ['error' => $e->getMessage()]);
                    // Don't fail the response — let the frontend keep polling
                }

                $order->refresh();
            }

            // ── Find the confirmed booking ────────────────────────────────────
            $booking = null;

            if ($order->booking_id) {
                $booking = Booking::find($order->booking_id);
            }

            if (!$booking) {
                $hold = BookingHold::where('payment_intent_id', $paymentIntentId)->first();
                if ($hold) {
                    $booking = Booking::where('booking_hold_id', $hold->id)->first();
                }
            }

            $bookingConfirmed = $booking && $booking->status === 'confirmed';
            $bookingReference = $booking?->booking_reference;

            return response()->json([
                'success'           => true,
                'payment_intent_id' => $paymentIntent->id,
                'payment_status'    => $paymentIntent->status,
                'order' => [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => $order->status,
                    'booking_id'   => $order->booking_id,
                ],
                'booking_confirmed' => $bookingConfirmed,
                'booking_reference' => $bookingReference,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
