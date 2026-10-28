import React, { useState } from 'react';
import { useStripe, useElements, PaymentElement } from '@stripe/react-stripe-js';

/**
 * Stripe payment form component
 * Handles card input and payment confirmation
 */
const StripePaymentForm = ({ onSuccess, onError, amount, currency = 'USD' }) => {
  const stripe = useStripe();
  const elements = useElements();
  const [isProcessing, setIsProcessing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const formatAmount = (amt) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0
    }).format(amt);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    setIsProcessing(true);
    setErrorMessage('');

    try {
      // Confirm the payment with Stripe
      const { error, paymentIntent } = await stripe.confirmPayment({
        elements,
        confirmParams: {
          return_url: `${window.location.origin}/confirming`,
        },
        redirect: 'if_required', // Stay on page if no 3D Secure required
      });

      if (error) {
        // Payment failed
        setErrorMessage(error.message);
        setIsProcessing(false);
        if (onError) {
          onError(error);
        }
      } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        // Payment succeeded in browser
        // CRITICAL: Do NOT show confirmation yet!
        // Wait for backend webhook to confirm
        if (onSuccess) {
          onSuccess(paymentIntent);
        }
      }
    } catch (err) {
      setErrorMessage(err.message || 'An unexpected error occurred');
      setIsProcessing(false);
      if (onError) {
        onError(err);
      }
    }
  };

  return (
    <form onSubmit={handleSubmit} className="stripe-payment-form">
      <h3>Payment Information</h3>

      <div className="payment-element-container">
        <PaymentElement />
      </div>

      {errorMessage && (
        <div className="error-alert">
          <strong>Payment Error:</strong> {errorMessage}
        </div>
      )}

      <div className="test-cards-info">
        <small>
          <strong>Test Cards:</strong><br />
          Success: 4242 4242 4242 4242<br />
          Declined: 4000 0000 0000 9995<br />
          Use any future date and any 3-digit CVC
        </small>
      </div>

      <button
        type="submit"
        className="btn btn-primary btn-large"
        disabled={!stripe || isProcessing}
      >
        {isProcessing ? (
          <>
            <span className="spinner"></span>
            Processing Payment...
          </>
        ) : (
          <>Pay {formatAmount(amount)}</>
        )}
      </button>

      <div className="secure-payment-badge">
        <span>🔒</span> Secure payment powered by Stripe
      </div>
    </form>
  );
};

export default StripePaymentForm;
