import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

/**
 * Helper Page - Redirects to Latest Confirmed Booking
 * Used for testing/debugging when stuck on confirming page
 */
const LatestBookingRedirect = () => {
  const navigate = useNavigate();
  const [error, setError] = useState('');

  useEffect(() => {
    fetchLatestBooking();
  }, []);

  const fetchLatestBooking = async () => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/test/latest-booking');
      const data = await response.json();

      if (data.success && data.booking_reference) {
        // Redirect to confirmation page
        navigate(`/confirmation/${data.booking_reference}`, { replace: true });
      } else {
        setError('No bookings found. Please start a new booking from the homepage.');
      }
    } catch (err) {
      console.error('Error fetching latest booking:', err);
      setError('Failed to fetch latest booking. Please try again.');
    }
  };

  return (
    <div className="confirming-page">
      <div className="container">
        <div className="confirming-card">
          {!error ? (
            <>
              <div className="spinner-large"></div>
              <h2>Finding Your Booking...</h2>
              <p className="text-muted">Redirecting to your latest confirmed booking...</p>
            </>
          ) : (
            <>
              <div className="error-icon">✗</div>
              <h2>No Booking Found</h2>
              <p>{error}</p>
              <button
                onClick={() => navigate('/')}
                className="btn btn-primary"
                style={{ marginTop: '2rem' }}
              >
                Return to Home
              </button>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default LatestBookingRedirect;
