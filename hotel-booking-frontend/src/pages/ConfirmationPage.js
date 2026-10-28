import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getBookingByReference } from '../services/api';
import { formatCurrency, formatDate } from '../utils/formatters';

/**
 * Page 6b: Booking Confirmation Page
 * 
 * Shown ONLY after webhook confirms the booking
 * Displays booking reference and summary
 */
const ConfirmationPage = () => {
  const { reference } = useParams();
  const navigate = useNavigate();
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (reference) {
      fetchBookingDetails();
    } else {
      navigate('/', { replace: true });
    }
  }, [reference]);

  const fetchBookingDetails = async () => {
    try {
      const response = await getBookingByReference(reference);
      if (response.success) {
        setBooking(response.booking);
      }
    } catch (err) {
      setError(err.message || 'Failed to load booking details');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="confirmation-page">
        <div className="container">
          <div className="loading">
            <div className="spinner"></div>
            <p>Loading booking details...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error || !booking) {
    return (
      <div className="confirmation-page">
        <div className="container">
          <div className="error-card">
            <h2>Booking Not Found</h2>
            <p>{error || 'Unable to find booking details'}</p>
            <Link to="/" className="btn btn-primary">
              Return to Home
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="confirmation-page">
      <div className="container">
        {/* Back Button */}
        <div style={{ marginBottom: '20px' }}>
          <button 
            onClick={() => navigate('/')} 
            style={{
              padding: '10px 20px',
              background: 'transparent',
              border: '2px solid #0071c2',
              borderRadius: '8px',
              color: '#0071c2',
              fontSize: '14px',
              fontWeight: '600',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              transition: 'all 0.2s'
            }}
            onMouseOver={(e) => {
              e.currentTarget.style.background = '#0071c2';
              e.currentTarget.style.color = 'white';
            }}
            onMouseOut={(e) => {
              e.currentTarget.style.background = 'transparent';
              e.currentTarget.style.color = '#0071c2';
            }}
          >
            <span>←</span> Back to Home
          </button>
        </div>

        <div className="confirmation-card">
          {/* Success Header */}
          <div className="success-header">
            <h1>Booking Confirmed!</h1>
            <p className="subtitle">Your reservation has been successfully confirmed</p>
          </div>

          {/* Booking Reference */}
          <div className="reference-section">
            <div className="reference-box">
              <span className="reference-label">Booking Reference</span>
              <span className="reference-number">{booking.reference}</span>
            </div>
            <p className="reference-note">
              Save this reference number. You'll need it to manage your booking.
            </p>
          </div>

          {/* Booking Details */}
          <div className="booking-details">
            <h3>Booking Details</h3>

            <div className="detail-section">
              <div className="detail-row">
                <span className="label">Property</span>
                <span className="value">{booking.property?.name}</span>
              </div>
              <div className="detail-row">
                <span className="label">Room Type</span>
                <span className="value">{booking.room_type?.name}</span>
              </div>
              <div className="detail-row">
                <span className="label">Check-in</span>
                <span className="value">{formatDate(booking.check_in, 'EEEE, MMMM dd, yyyy')}</span>
              </div>
              <div className="detail-row">
                <span className="label">Check-out</span>
                <span className="value">{formatDate(booking.check_out, 'EEEE, MMMM dd, yyyy')}</span>
              </div>
              <div className="detail-row">
                <span className="label">Guests</span>
                <span className="value">{booking.guests}</span>
              </div>
            </div>

            {/* Guest Information */}
            <h4>Guest Information</h4>
            <div className="detail-section">
              <div className="detail-row">
                <span className="label">Name</span>
                <span className="value">{booking.guest?.first_name} {booking.guest?.last_name}</span>
              </div>
              <div className="detail-row">
                <span className="label">Email</span>
                <span className="value">{booking.guest?.email}</span>
              </div>
              <div className="detail-row">
                <span className="label">Phone</span>
                <span className="value">{booking.guest?.phone}</span>
              </div>
            </div>

            {/* Payment Information */}
            {booking.order && (
              <>
                <h4>Payment Summary</h4>
                <div className="detail-section">
                  <div className="detail-row">
                    <span className="label">Total Paid</span>
                    <span className="value highlight">
                      {formatCurrency(booking.order.grand_total)}
                    </span>
                  </div>
                  <div className="detail-row">
                    <span className="label">Payment Status</span>
                    <span className="value">
                      <span className="badge badge-success">Paid</span>
                    </span>
                  </div>
                  {booking.order.invoice_number && (
                    <div className="detail-row">
                      <span className="label">Invoice</span>
                      <span className="value">
                        <Link to={`/invoice/${booking.order.invoice_number}`}>
                          View Invoice
                        </Link>
                      </span>
                    </div>
                  )}
                </div>
              </>
            )}
          </div>

          {/* Next Steps */}
          <div className="next-steps">
            <h3>What's Next?</h3>
            <ul>
              <li>✉️ A confirmation email has been sent to {booking.guest?.email}</li>
              <li>📄 You can download your invoice using the link above</li>
              <li>🏨 Please arrive after 3:00 PM on your check-in date</li>
              <li>📞 Contact the property if you have any special requests</li>
            </ul>
          </div>

          {/* Actions */}
          <div className="confirmation-actions">
            <Link to="/" className="btn btn-secondary">
              Return to Home
            </Link>
            {booking.order?.invoice_number && (
              <Link
                to={`/invoice/${booking.order.invoice_number}`}
                className="btn btn-primary"
              >
                View Invoice
              </Link>
            )}
          </div>
        </div>

        {/* Email reminder */}
        <div className="email-reminder">
          <p>
            <strong>📧 Check your email</strong><br />
            A confirmation email with all booking details has been sent to{' '}
            <strong>{booking.guest?.email}</strong>
          </p>
        </div>
      </div>
    </div>
  );
};

export default ConfirmationPage;
