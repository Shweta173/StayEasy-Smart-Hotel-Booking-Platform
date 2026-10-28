import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import GuestDetailsForm from '../components/GuestDetailsForm';
import StripePaymentForm from '../components/StripePaymentForm';
import PriceSummary from '../components/PriceSummary';
import CountdownTimer from '../components/CountdownTimer';
import AuthRequiredModal from '../components/AuthRequiredModal';
import { createBookingHold, createPaymentIntent, getSettings, getPricingBreakdown } from '../services/api';
import { formatCurrency, formatCountdown } from '../utils/formatters';
import { STRIPE_PUBLISHABLE_KEY, HOLD_EXPIRY_MINUTES } from '../config';

// Initialize Stripe
const stripePromise = loadStripe(STRIPE_PUBLISHABLE_KEY);

/**
 * Page 5: Checkout Page
 * 
 * Implements the complete checkout flow following PDF requirements:
 * 1. Collect guest details
 * 2. Create booking hold (15 minutes)
 * 3. Show countdown timer
 * 4. Create Stripe PaymentIntent
 * 5. Display payment form
 * 6. Handle payment confirmation
 * 7. Poll backend for webhook confirmation (CRITICAL!)
 */
const CheckoutPage = () => {
  const navigate = useNavigate();

  // Retrieve booking data from localStorage (set by RoomSelectionPage)
  const [bookingData, setBookingData] = useState(null);

  // State management
  const [settings, setSettings] = useState(null); // VAT settings
  const [realTimePricing, setRealTimePricing] = useState(null); // Real-time pricing from backend
  const [step, setStep] = useState(1); // 1 = guest details, 2 = payment
  const [guestDetails, setGuestDetails] = useState(null);
  const [hold, setHold] = useState(null);
  const [order, setOrder] = useState(null);
  const [clientSecret, setClientSecret] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showAuthModal, setShowAuthModal] = useState(false);

  // Load booking data from localStorage
  useEffect(() => {
    const data = localStorage.getItem('booking_data');
    if (data) {
      try {
        const parsed = JSON.parse(data);
        
        // Validate booking data has all required fields
        if (!parsed.roomTypeId || !parsed.checkIn || !parsed.checkOut || !parsed.nights) {
          console.error('Invalid booking data - missing required fields');
          localStorage.removeItem('booking_data');
          navigate('/', { replace: true });
          return;
        }
        
        // Validate minimum nights requirement
        // This catches cases where user tried to book with insufficient nights
        if (parsed.nights < 1) {
          console.error('Invalid booking data - nights < 1');
          localStorage.removeItem('booking_data');
          navigate('/', { replace: true });
          return;
        }
        
        setBookingData(parsed);
      } catch (err) {
        console.error('Failed to parse booking data:', err);
        localStorage.removeItem('booking_data');
        navigate('/', { replace: true });
      }
    } else {
      // No booking data - redirect to home
      navigate('/', { replace: true });
    }
    
    // Check if user is authenticated
    const token = localStorage.getItem('auth_token');
    if (!token) {
      // User not logged in - show auth modal
      setShowAuthModal(true);
    }
  }, [navigate]);

  // Load settings for VAT display logic
  useEffect(() => {
    const loadSettings = async () => {
      try {
        const response = await getSettings();
        if (response.success) {
          setSettings(response.settings);
        }
      } catch (err) {
        console.error('Failed to load settings:', err);
        // Use default settings if API fails
        setSettings({
          vat_registered: true,
          default_vat_rate: 10.00,
          prices_include_vat: false,
        });
      }
    };
    
    loadSettings();
  }, []);

  // Load real-time pricing from backend when booking data is available
  useEffect(() => {
    const loadRealTimePricing = async () => {
      if (!bookingData) return;
      
      // Don't poll if we're on Step 2 (payment) - price is locked in the order
      if (step === 2 || order) return;
      
      try {
        console.log('Fetching real-time pricing for:', {
          room_type_id: bookingData.roomTypeId,
          check_in: bookingData.checkIn,
          check_out: bookingData.checkOut,
          num_rooms: bookingData.rooms || 1
        });
        
        const response = await getPricingBreakdown({
          room_type_id: bookingData.roomTypeId,
          check_in: bookingData.checkIn,
          check_out: bookingData.checkOut,
          num_rooms: bookingData.rooms || 1
        });

        if (response.success) {
          console.log('Real-time pricing loaded:', response.pricing);
          
          // Check if price changed
          const newPrice = parseFloat(response.pricing.pricing?.grand_total?.replace(/,/g, '') || 0);
          const oldPrice = realTimePricing ? parseFloat(realTimePricing.pricing?.grand_total?.replace(/,/g, '') || 0) : null;
          
          if (oldPrice && Math.abs(newPrice - oldPrice) > 0.01) {
            console.log('🔔 Price changed!', { oldPrice, newPrice });
          }
          
          setRealTimePricing(response.pricing);
        }
      } catch (err) {
        console.error('Failed to load real-time pricing:', err);
        // Keep using cached pricing if API fails
      }
    };
    
    // Load immediately
    loadRealTimePricing();
    
    // Only poll on Step 1 (Guest Details), not on Step 2 (Payment)
    if (step === 1 && !order) {
      // Then poll every 5 seconds to check for price updates
      const pollingInterval = setInterval(() => {
        console.log('Polling for price updates...');
        loadRealTimePricing();
      }, 5000); // Check every 5 seconds
      
      // Cleanup: Stop polling when component unmounts or step changes
      return () => {
        clearInterval(pollingInterval);
        console.log('Stopped price polling');
      };
    }
  }, [bookingData, step, order]);


  /**
   * Step 1: Handle guest details submission
   * Creates booking hold on backend
   */
  const handleGuestDetailsSubmit = async (formData) => {
    setLoading(true);
    setError('');

    try {
      // Prepare booking hold request data
      const holdRequest = {
        room_type_id: bookingData.roomTypeId,
        check_in: bookingData.checkIn,
        check_out: bookingData.checkOut,
        num_rooms: bookingData.rooms || 1, // Include number of rooms
        num_guests: bookingData.guests || 1, // Include number of guests
        guest: {
          first_name: formData.first_name,
          last_name: formData.last_name,
          email: formData.email,
          phone: formData.phone
        },
        special_requests: formData.special_requests
      };

      // Debug: Log the request data
      console.log('Creating booking hold with data:', holdRequest);
      console.log('Room Type ID:', bookingData.roomTypeId, 'Type:', typeof bookingData.roomTypeId);

      // Create booking hold with guest details
      const response = await createBookingHold(holdRequest);

      if (response.success) {
        // Hold created successfully
        setGuestDetails(formData);
        setHold(response.hold);
        setOrder(response.order);

        // Debug: Log the order details
        console.log('Order created with pricing:', {
          subtotal: response.order.subtotal_ex_vat,
          tax: response.order.tax_amount,
          grand_total: response.order.grand_total_inc_vat,
          items: response.order.items
        });

        // Create payment intent
        await handleCreatePaymentIntent(response.order.id, response.hold.id);

        // Move to payment step
        setStep(2);
      } else {
        setError(response.message || 'Failed to create booking hold');
      }
    } catch (err) {
      console.error('Booking hold error:', err);
      setError(err.message || 'Failed to create booking hold');
    } finally {
      setLoading(false);
    }
  };

  /**
   * Step 2: Create Stripe PaymentIntent
   */
  const handleCreatePaymentIntent = async (orderId, holdId) => {
    try {
      const response = await createPaymentIntent({
        order_id: orderId,
        hold_id: holdId
      });

      if (response.success) {
        setClientSecret(response.payment_intent.client_secret);
      }
    } catch (err) {
      setError(err.message || 'Failed to create payment intent');
      throw err;
    }
  };

  /**
   * Step 3: Handle payment success
   * CRITICAL: Don't show confirmation yet!
   * Navigate to polling page that waits for webhook
   */
  const handlePaymentSuccess = (paymentIntent) => {
    // Payment succeeded in browser
    // NOW navigate to confirmation page that polls for webhook
    navigate('/confirming', {
      state: {
        payment_intent_id: paymentIntent.id,
        order_id: order.id,
        hold_id: hold.id
      }
    });
  };

  /**
   * Handle payment error
   * Guest can retry without losing hold
   */
  const handlePaymentError = (error) => {
    setError(error.message || 'Payment failed. Please try again.');
  };

  /**
   * Handle hold expiry
   */
  const handleHoldExpired = () => {
    alert('Your booking hold has expired. Please start over.');
    navigate('/', { replace: true });
  };

  if (!bookingData) {
    return null;
  }

  return (
    <div className="checkout-page">
      {/* Authentication Required Modal */}
      {showAuthModal && (
        <AuthRequiredModal onClose={() => {
          setShowAuthModal(false);
          navigate('/', { replace: true });
        }} />
      )}

      <div className="container">
        <div className="checkout-header">
          <h1>Complete Your Booking</h1>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
            <button 
              onClick={() => navigate(-1)} 
              style={{ 
                background: 'none',
                border: 'none',
                color: '#0066cc',
                cursor: 'pointer',
                fontSize: '14px',
                padding: '0',
                textDecoration: 'none',
                fontWeight: 'normal'
              }}
            >
              ← Back
            </button>
            <div className="steps-indicator">
              <div className={`step ${step === 1 ? 'active' : 'completed'}`}>
                <span className="step-number">1</span>
                <span className="step-label">Guest Details</span>
              </div>
              <div className={`step ${step === 2 ? 'active' : ''}`}>
                <span className="step-number">2</span>
                <span className="step-label">Payment</span>
              </div>
            </div>
          </div>
        </div>

        {error && (
          <div className="alert alert-error">
            {error}
          </div>
        )}

        {/* Show countdown timer after hold is created */}
        {hold && (
          <CountdownTimer
            expiresAt={hold.expires_at}
            onExpire={handleHoldExpired}
          />
        )}

        <div className="checkout-content">
          <div className="checkout-main">
            {/* Step 1: Guest Details Form */}
            {step === 1 && (
              <GuestDetailsForm
                onSubmit={handleGuestDetailsSubmit}
                initialData={guestDetails || {}}
                loading={loading}
              />
            )}

            {/* Step 2: Payment Form */}
            {step === 2 && clientSecret && order && (
              <Elements
                stripe={stripePromise}
                options={{
                  clientSecret,
                  appearance: {
                    theme: 'stripe',
                    variables: {
                      colorPrimary: '#003580',
                      colorBackground: '#ffffff',
                      colorText: '#333333',
                      fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    },
                  },
                }}
              >
                <StripePaymentForm
                  onSuccess={handlePaymentSuccess}
                  onError={handlePaymentError}
                  amount={parseFloat(order.grand_total_inc_vat || order.grand_total || 0)}
                  currency={order.currency || "USD"}
                />
              </Elements>
            )}

            {loading && (
              <div className="loading-overlay">
                <div className="spinner"></div>
                <p>Processing...</p>
              </div>
            )}
          </div>

          {/* Price Summary Sidebar */}
          <div className="checkout-sidebar">
            {bookingData && (
              <>
                <PriceSummary
                  booking={{
                    property_name: bookingData.propertyName,
                    room_type_name: bookingData.roomTypeName,
                    check_in: bookingData.checkIn,
                    check_out: bookingData.checkOut,
                    guests: bookingData.guests,
                    rooms: bookingData.rooms || 1  // Include number of rooms
                  }}
                  pricing={
                    order ? {
                      // USE REAL ORDER DATA FROM BACKEND (after hold is created - Step 2)
                      nights: bookingData.nights,
                      rooms: bookingData.rooms || 1,
                      nightly_rates: order.items?.filter(item => item.type === 'room_night').map(item => ({
                        date: item.description.split(' - ')[1]?.split(' (')[0] || '',
                        price: typeof item.unit_price === 'string' ? parseFloat(item.unit_price.replace(/,/g, '')) : item.unit_price
                      })),
                      subtotal: parseFloat(order.subtotal_ex_vat),
                      tax_rate: order.tax_rate + '%',
                      tax_total: parseFloat(order.tax_amount),
                      grand_total: parseFloat(order.grand_total_inc_vat || order.grand_total),
                      currency: order.currency
                    } : realTimePricing ? {
                      // USE REAL-TIME PRICING FROM BACKEND (Step 1 - before hold created)
                      nights: realTimePricing.stay_details?.nights || bookingData.nights,
                      rooms: realTimePricing.stay_details?.num_rooms || bookingData.rooms || 1,
                      nightly_rates: realTimePricing.nightly_rates?.map(rate => ({
                        date: rate.date,
                        price: parseFloat(rate.price_per_room?.replace(/,/g, '') || rate.price_per_room)
                      })),
                      subtotal: parseFloat(realTimePricing.pricing?.subtotal?.replace(/,/g, '') || realTimePricing.pricing?.subtotal),
                      tax_rate: realTimePricing.pricing?.tax_rate || '0%',
                      tax_total: parseFloat(realTimePricing.pricing?.tax_amount?.replace(/,/g, '') || realTimePricing.pricing?.tax_amount),
                      grand_total: parseFloat(realTimePricing.pricing?.grand_total?.replace(/,/g, '') || realTimePricing.pricing?.grand_total),
                      currency: realTimePricing.pricing?.currency || 'USD'
                    } : {
                      // USE CACHED DATA FROM LOCALSTORAGE (before hold is created)
                      nights: bookingData.nights,
                      rooms: bookingData.rooms || 1,
                      price_per_night: bookingData.pricePerNight,
                      nightly_rates: bookingData.pricing_details?.nightly_rates,
                      subtotal: bookingData.pricePerNight * bookingData.nights * (bookingData.rooms || 1),
                      tax_rate: (settings?.default_vat_rate || 10) + '%',
                      tax_total: bookingData.pricePerNight * bookingData.nights * (bookingData.rooms || 1) * ((settings?.default_vat_rate || 10) / 100),
                      grand_total: bookingData.pricePerNight * bookingData.nights * (bookingData.rooms || 1) * (1 + ((settings?.default_vat_rate || 10) / 100)),
                      currency: 'USD'
                    }
                  }
                  settings={settings}
                />
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckoutPage;
