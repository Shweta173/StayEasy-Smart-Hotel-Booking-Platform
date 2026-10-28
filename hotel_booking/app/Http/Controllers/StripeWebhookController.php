<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\OrderService;
use App\Services\InvoiceService;
use App\Models\Payment;
use App\Models\BookingHold;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected $bookingService;
    protected $orderService;
    protected $invoiceService;

    public function __construct(
        BookingService $bookingService,
        OrderService $orderService,
        InvoiceService $invoiceService
    ) {
        $this->bookingService = $bookingService;
        $this->orderService = $orderService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Handle Stripe webhook events
     * 
     * CRITICAL: This is the source of truth for payments (Rule #2 from PDF)
     * "A payment is only real once Stripe tells the backend directly via webhook"
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );

        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook: Invalid signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleRefund($event->data->object);
                    break;

                default:
                    Log::info('Stripe webhook: Unhandled event type', ['type' => $event->type]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error processing event', [
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful payment
     * This is where the booking is CONFIRMED (not before!)
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        Log::info('Payment succeeded', ['payment_intent_id' => $paymentIntent->id]);

        // Find the payment record
        $payment = Payment::where('stripe_id', $paymentIntent->id)->first();

        if (!$payment) {
            Log::error('Payment record not found', ['payment_intent_id' => $paymentIntent->id]);
            return;
        }

        // Check if already processed
        if ($payment->status === 'succeeded') {
            Log::info('Payment already processed', ['payment_id' => $payment->id]);
            return;
        }

        $order = $payment->order;

        // Find the hold
        $hold = BookingHold::where('payment_intent_id', $paymentIntent->id)->first();

        if (!$hold) {
            Log::error('Hold not found for payment intent', ['payment_intent_id' => $paymentIntent->id]);
            
            // Mark payment and order as failed
            $payment->update(['status' => 'failed']);
            $order->update(['status' => 'failed']);
            
            return;
        }

        // *** CRITICAL: Validate room is still available ***
        $isAvailable = $this->bookingService->validateRoomAvailability(
            $hold->room_type_id,
            $hold->check_in,
            $hold->check_out,
            $hold->id // Exclude this hold from the check
        );

        if (!$isAvailable) {
            Log::error('Room no longer available at payment confirmation', [
                'hold_id' => $hold->id,
                'room_type_id' => $hold->room_type_id,
                'check_in' => $hold->check_in->toDateString(),
                'check_out' => $hold->check_out->toDateString(),
            ]);

            // Mark payment as failed
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'failed_at' => now()->toIso8601String(),
                    'failure_reason' => 'Room no longer available',
                ]),
            ]);

            // Mark order as failed
            $order->update(['status' => 'failed']);

            // Mark hold as expired/cancelled
            $hold->update(['status' => 'expired']);

            // Refund the payment immediately
            try {
                $refund = \Stripe\Refund::create([
                    'payment_intent' => $paymentIntent->id,
                    'reason' => 'requested_by_customer',
                ]);

                Log::info('Payment refunded - room unavailable', [
                    'payment_intent_id' => $paymentIntent->id,
                    'refund_id' => $refund->id,
                ]);

                // Create refund payment record
                Payment::create([
                    'order_id' => $order->id,
                    'stripe_id' => $refund->id,
                    'amount' => $payment->amount,
                    'type' => 'refund',
                    'status' => 'succeeded',
                    'metadata' => [
                        'refund_id' => $refund->id,
                        'original_payment_id' => $payment->id,
                        'reason' => 'Room unavailable at confirmation',
                    ],
                ]);

                // Update order to refunded status
                $order->update(['status' => 'refunded']);

            } catch (\Exception $e) {
                Log::error('Failed to refund payment for unavailable room', [
                    'payment_intent_id' => $paymentIntent->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        // Update payment status
        $payment->update([
            'status' => 'succeeded',
            'metadata' => array_merge($payment->metadata ?? [], [
                'processed_at' => now()->toIso8601String(),
                'amount_received' => $paymentIntent->amount_received / 100,
            ]),
        ]);

        // Update order status
        $this->orderService->markOrderAsPaid($order);

        // Convert hold to confirmed booking
        $booking = $this->bookingService->confirmBooking($hold, $order);

        // Generate invoice
        $invoice = $this->invoiceService->generateInvoice($order);

        Log::info('Booking confirmed', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'invoice_number' => $invoice->invoice_number,
        ]);

        // Send confirmation email to guest
        try {
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendBookingConfirmation($booking, $invoice);
            Log::info('Confirmation email sent', ['booking_id' => $booking->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the webhook if email fails
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($paymentIntent)
    {
        Log::warning('Payment failed', ['payment_intent_id' => $paymentIntent->id]);

        $payment = Payment::where('stripe_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'failed_at' => now()->toIso8601String(),
                    'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown',
                ]),
            ]);
        }

        // Hold will expire naturally, freeing the room
    }

    /**
     * Handle refund
     */
    private function handleRefund($charge)
    {
        Log::info('Refund processed', ['charge_id' => $charge->id]);

        // Find the original payment
        $payment = Payment::where('stripe_id', $charge->payment_intent)->first();

        if ($payment) {
            // Update order status
            $payment->order->update(['status' => 'refunded']);

            Log::info('Order marked as refunded', ['order_id' => $payment->order_id]);
        }
    }
}
