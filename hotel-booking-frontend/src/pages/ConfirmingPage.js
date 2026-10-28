import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { checkPaymentStatus } from '../services/api';
import { PAYMENT_POLLING_INTERVAL, PAYMENT_POLLING_MAX_ATTEMPTS } from '../config';

/**
 * Page 6a: Confirming Page (Payment Polling)
 * 
 * CRITICAL PAGE - Implements PDF Rule #2:
 * "A payment is only real once Stripe tells the backend directly via webhook.
 * The frontend saying 'paid' is never enough."
 * 
 * This page polls the backend until the webhook confirms the booking.
 * Do NOT show confirmation until booking_confirmed = true!
 */
const ConfirmingPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { payment_intent_id, order_id, hold_id } = location.state || {};

  const [status, setStatus] = useState('processing');
  const [attempts, setAttempts] = useState(0);
  const [message, setMessage] = useState('Processing your payment...');

  useEffect(() => {
    // Validate we have payment intent ID
    if (!payment_intent_id) {
      setStatus('error');
      setMessage('No payment information found. Redirecting to home...');
      setTimeout(() => {
        navigate('/', { replace: true });
      }, 2000);
      return;
    }

    // TEST MODE: Auto-confirm booking for local development
    if (process.env.NODE_ENV === 'development') {
      autoConfirmBooking();
    } else {
      // Start polling for payment confirmation
      pollPaymentStatus();
    }
  }, [payment_intent_id]);

  /**
   * TEST MODE: Auto-confirm booking (for local dev without webhooks)
   * In production, webhooks handle this automatically
   */
  const autoConfirmBooking = async () => {
    try {
      setMessage('Auto-confirming booking (TEST MODE)...');
      
      // Trigger test endpoint to confirm booking
      const response = await fetch(`http://127.0.0.1:8000/api/test/auto-confirm/${payment_intent_id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Wait a moment then poll to confirm
        setTimeout(() => {
          pollPaymentStatus();
        }, 1000);
      } else if (data.validation_failed) {
        // Validation failed - show appropriate error
        setStatus('failed');
        
        if (data.price_validation_failed) {
          // Price changed
          setMessage(`Price changed during checkout. Original: $${data.original_amount}, New: $${data.current_amount}.`);
        } else {
          // Room unavailable
          setMessage('Room is no longer available.');
        }
        
        setTimeout(() => {
          navigate('/', { replace: true });
        }, 6000);
      } else {
        // Other errors - fallback to normal polling
        pollPaymentStatus();
      }
    } catch (error) {
      console.error('Auto-confirm error:', error);
      // Fallback to normal polling
      pollPaymentStatus();
    }
  };

  /**
   * Poll backend for payment status
   * Continues until webhook confirms booking or max attempts reached
   */
  const pollPaymentStatus = async () => {
    let currentAttempt = 0;
    
    const pollInterval = setInterval(async () => {
      try {
        currentAttempt++;
        setAttempts(currentAttempt);

        // Check payment status on backend
        const response = await checkPaymentStatus(payment_intent_id);

        if (response.success) {
          // Check if webhook has confirmed the booking
          if (response.booking_confirmed && response.booking_reference) {
            // SUCCESS! Webhook has processed
            clearInterval(pollInterval);
            setStatus('confirmed');
            setMessage('Payment confirmed! Redirecting...');

            // Navigate to confirmation page after 8 seconds
            setTimeout(() => {
              navigate(`/confirmation/${response.booking_reference}`, {
                replace: true
              });
            }, 8000);
            return;
          }

          // Payment succeeded but webhook not yet processed
          if (response.payment_status === 'succeeded') {
            setMessage('Payment received. Confirming your booking...');
          }

          // Payment failed
          if (response.payment_status === 'failed' || response.status === 'failed') {
            clearInterval(pollInterval);
            setStatus('failed');
            setMessage('');  // Clear the previous message
            setTimeout(() => {
              navigate('/', { replace: true });
            }, 5000);
            return;
          }
        }

        // Stop polling after max attempts
        if (currentAttempt >= PAYMENT_POLLING_MAX_ATTEMPTS) {
          clearInterval(pollInterval);
          setStatus('timeout');
          setMessage(
            'Taking longer than expected. Checking if your booking was already confirmed...'
          );
          
          // Try one last time to get the booking reference
          setTimeout(async () => {
            try {
              const finalCheck = await checkPaymentStatus(payment_intent_id);
              if (finalCheck.success && finalCheck.booking_reference) {
                navigate(`/confirmation/${finalCheck.booking_reference}`, {
                  replace: true
                });
              } else {
                setMessage(
                  'Your payment may still be processing. Please check your email for confirmation or contact support if needed.'
                );
              }
            } catch (err) {
              console.error('Final check error:', err);
            }
          }, 2000);
        }
      } catch (error) {
        console.error('Polling error:', error);
        // Continue polling even on error (might be temporary network issue)
      }
    }, PAYMENT_POLLING_INTERVAL);

    // Cleanup on unmount
    return () => clearInterval(pollInterval);
  };

  return (
    <div className="confirming-page">
      <div className="container">
        <div className="confirming-card">
          {/* Processing State */}
          {status === 'processing' && (
            <>
              <div className="spinner-large"></div>
              <h2>{message}</h2>
              <p className="text-muted">Please wait while we confirm your booking...</p>
              <div className="status-info">
                <p>
                  <strong>What's happening?</strong><br />
                  Your payment has been processed by Stripe. We're now waiting for
                  payment confirmation from our secure payment processor. This usually
                  takes just a few seconds.
                </p>
                <p className="polling-info">
                  Checking status... (Attempt {attempts} of {PAYMENT_POLLING_MAX_ATTEMPTS})
                </p>
              </div>
              <div className="warning-box">
                <strong>⚠️ Do not close this page!</strong><br />
                Your booking is being confirmed. Closing this page won't affect your
                reservation, but you may need to check your email for the confirmation.
              </div>
            </>
          )}

          {/* Confirmed State */}
          {status === 'confirmed' && (
            <>
              <div className="success-icon">✓</div>
              <h2>Payment Confirmed!</h2>
              <p>Your booking has been successfully confirmed.</p>
            </>
          )}

          {/* Failed State */}
          {status === 'failed' && (
            <>
              <div className="error-icon">✗</div>
              <h2>Payment Failed</h2>
              <p>Your payment could not be processed. Room not Available</p>
            </>
          )}

          {/* Timeout State */}
          {status === 'timeout' && (
            <>
              <div className="warning-icon">⚠️</div>
              <h2>Checking Status...</h2>
              <p>{message}</p>
              <div className="timeout-actions">
                <button
                  onClick={() => navigate('/')}
                  className="btn btn-secondary"
                >
                  Return to Home
                </button>
                <button
                  onClick={() => window.location.reload()}
                  className="btn btn-secondary"
                >
                  Refresh Page
                </button>
              </div>
            </>
          )}

          {/* Error State */}
          {status === 'error' && (
            <>
              <div className="error-icon">✗</div>
              <h2>Error</h2>
              <p>{message}</p>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ConfirmingPage;
