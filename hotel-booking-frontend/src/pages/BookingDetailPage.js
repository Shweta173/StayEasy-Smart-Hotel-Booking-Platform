import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getBookingDetails } from '../services/api';
import './BookingDetailPage.css';

/**
 * Premium Booking Detail Page (Page 9)
 * 
 * Shows complete booking details: dates, room, order line items, payment status
 * Inspired by Booking.com and Airbnb confirmation pages
 */
function BookingDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem('guest_token');
    if (!token) {
      navigate(`/login?redirect=/booking/${id}`);
      return;
    }

    loadBookingDetails();
  }, [id, navigate]);

  const loadBookingDetails = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await getBookingDetails(id);
      
      if (response.success) {
        setBooking(response.data);
      } else {
        setError(response.message || 'Failed to load booking details');
      }
    } catch (err) {
      console.error('Error loading booking:', err);
      setError(err.message || 'Failed to load booking details');
      
      // If authentication error, redirect to login
      if (err.message.includes('Unauthenticated') || err.message.includes('401')) {
        localStorage.removeItem('guest_token');
        localStorage.removeItem('guest_data');
        navigate(`/login?redirect=/booking/${id}`);
      }
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatShortDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const getStatusBadgeClass = (status) => {
    const statusMap = {
      'confirmed': 'badge-confirmed',
      'checked_in': 'badge-checked-in',
      'checked_out': 'badge-completed',
      'cancelled': 'badge-cancelled',
      'pending': 'badge-pending'
    };
    return statusMap[status] || 'badge-pending';
  };

  const getStatusLabel = (status) => {
    const labelMap = {
      'confirmed': 'Confirmed',
      'checked_in': 'Checked In',
      'checked_out': 'Checked Out',
      'cancelled': 'Cancelled',
      'pending': 'Pending'
    };
    return labelMap[status] || status;
  };

  const getPaymentStatusBadgeClass = (status) => {
    const statusMap = {
      'paid': 'payment-paid',
      'pending': 'payment-pending',
      'failed': 'payment-failed',
      'refunded': 'payment-refunded'
    };
    return statusMap[status] || 'payment-pending';
  };

  if (loading) {
    return (
      <div className="premium-booking-detail">
        <div className="detail-container">
          <div className="loading-state">
            <div className="spinner"></div>
            <p>Loading booking details...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="premium-booking-detail">
        <div className="detail-container">
          <div className="error-state">
            <div className="error-icon">⚠️</div>
            <h2>Error Loading Booking</h2>
            <p>{error}</p>
            <Link to="/my-bookings" className="btn-primary">
              ← Back to My Bookings
            </Link>
          </div>
        </div>
      </div>
    );
  }

  if (!booking) {
    return null;
  }

  return (
    <div className="premium-booking-detail">
      <div className="detail-container">
        {/* Header */}
        <div className="detail-header">
          <Link to="/my-bookings" className="back-link">
            ← Back to My Bookings
          </Link>
          <div className="header-content">
            <div className="header-left">
              <h1>Booking Details</h1>
              <p className="booking-ref">Reference: {booking.booking.booking_reference}</p>
            </div>
            <span className={`status-badge ${getStatusBadgeClass(booking.booking.status)}`}>
              {getStatusLabel(booking.booking.status)}
            </span>
          </div>
        </div>

        {/* Main Content Grid */}
        <div className="detail-grid">
          {/* Left Column - Main Details */}
          <div className="left-column">
            {/* Property & Room Info */}
            <div className="detail-card">
              <div className="card-header">
                <h2>{booking.property.name}</h2>
                <p className="property-location">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                  </svg>
                  {booking.property.city}, {booking.property.state}, {booking.property.country}
                </p>
              </div>
              
              <div className="card-body">
                <div className="room-info">
                  <h3>{booking.room_type.name}</h3>
                  <p className="room-description">{booking.room_type.description}</p>
                  <div className="room-meta">
                    <span>👥 Max {booking.room_type.max_occupancy} guests</span>
                  </div>
                </div>

                {booking.property.address && (
                  <div className="property-contact">
                    <h4>Property Contact</h4>
                    <p>📍 {booking.property.address}</p>
                    {booking.property.phone && <p>📞 {booking.property.phone}</p>}
                    {booking.property.email && <p>📧 {booking.property.email}</p>}
                  </div>
                )}
              </div>
            </div>

            {/* Stay Details */}
            <div className="detail-card">
              <div className="card-header">
                <h2>Stay Details</h2>
              </div>
              <div className="card-body">
                <div className="stay-timeline">
                  <div className="timeline-item">
                    <div className="timeline-icon checkin">📅</div>
                    <div className="timeline-content">
                      <span className="timeline-label">Check-in</span>
                      <span className="timeline-date">{formatDate(booking.booking.check_in)}</span>
                      <span className="timeline-time">After 3:00 PM</span>
                    </div>
                  </div>
                  <div className="timeline-divider">
                    <span className="nights-badge">{booking.booking.nights} {booking.booking.nights === 1 ? 'Night' : 'Nights'}</span>
                  </div>
                  <div className="timeline-item">
                    <div className="timeline-icon checkout">📅</div>
                    <div className="timeline-content">
                      <span className="timeline-label">Check-out</span>
                      <span className="timeline-date">{formatDate(booking.booking.check_out)}</span>
                      <span className="timeline-time">Before 11:00 AM</span>
                    </div>
                  </div>
                </div>

                <div className="stay-meta">
                  <div className="meta-item">
                    <span className="meta-icon">👥</span>
                    <div>
                      <span className="meta-label">Guests</span>
                      <span className="meta-value">{booking.booking.guests} {booking.booking.guests === 1 ? 'Adult' : 'Adults'}</span>
                    </div>
                  </div>
                  <div className="meta-item">
                    <span className="meta-icon">📆</span>
                    <div>
                      <span className="meta-label">Booked on</span>
                      <span className="meta-value">{formatShortDate(booking.booking.created_at)}</span>
                    </div>
                  </div>
                </div>

                {booking.booking.special_requests && (
                  <div className="special-requests">
                    <h4>Special Requests</h4>
                    <p>{booking.booking.special_requests}</p>
                  </div>
                )}
              </div>
            </div>

            {/* Guest Information */}
            <div className="detail-card">
              <div className="card-header">
                <h2>Guest Information</h2>
              </div>
              <div className="card-body">
                <div className="guest-details">
                  <div className="guest-item">
                    <span className="guest-icon">👤</span>
                    <div>
                      <span className="guest-label">Name</span>
                      <span className="guest-value">{booking.guest.first_name} {booking.guest.last_name}</span>
                    </div>
                  </div>
                  <div className="guest-item">
                    <span className="guest-icon">📧</span>
                    <div>
                      <span className="guest-label">Email</span>
                      <span className="guest-value">{booking.guest.email}</span>
                    </div>
                  </div>
                  {booking.guest.phone && (
                    <div className="guest-item">
                      <span className="guest-icon">📞</span>
                      <div>
                        <span className="guest-label">Phone</span>
                        <span className="guest-value">{booking.guest.phone}</span>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Right Column - Order Summary */}
          <div className="right-column">
            {/* Order Summary Card */}
            {booking.order && (
              <div className="order-card sticky">
                <div className="order-header">
                  <h2>Order Summary</h2>
                  <span className={`payment-badge ${getPaymentStatusBadgeClass(booking.order.status)}`}>
                    {booking.order.status === 'paid' ? '✓ Paid' : booking.order.status.toUpperCase()}
                  </span>
                </div>

                <div className="order-body">
                  <div className="order-info">
                    <span className="order-label">Order Number</span>
                    <span className="order-value">{booking.order.order_number}</span>
                  </div>

                  {/* Price Breakdown */}
                  <div className="price-breakdown">
                    <h3>Charges</h3>
                    
                    <div className="price-item">
                      <span>Room rate ({booking.booking.nights} {booking.booking.nights === 1 ? 'night' : 'nights'})</span>
                      <span>{formatCurrency(booking.order.subtotal)}</span>
                    </div>

                    {booking.order.tax_total > 0 && (
                      <div className="price-item">
                        <span>Taxes (10%)</span>
                        <span>{formatCurrency(booking.order.tax_total)}</span>
                      </div>
                    )}

                    {booking.order.service_fee > 0 && (
                      <div className="price-item">
                        <span>Service Fee (5%)</span>
                        <span>{formatCurrency(booking.order.service_fee)}</span>
                      </div>
                    )}

                    <div className="price-divider"></div>

                    <div className="price-total">
                      <span>Grand Total</span>
                      <span>{formatCurrency(booking.order.grand_total)}</span>
                    </div>
                  </div>

                  {/* Invoice Section */}
                  {booking.invoice && (
                    <div className="invoice-section">
                      <div className="invoice-info">
                        <div className="invoice-row">
                          <span>Invoice Number</span>
                          <span>{booking.invoice.invoice_number}</span>
                        </div>
                        <div className="invoice-row">
                          <span>Issue Date</span>
                          <span>{formatShortDate(booking.invoice.issued_at)}</span>
                        </div>
                        <div className="invoice-row">
                          <span>Status</span>
                          <span className={`badge-${booking.invoice.status}`}>
                            {booking.invoice.status.toUpperCase()}
                          </span>
                        </div>
                      </div>
                      <Link
                        to={`/invoice/${booking.invoice.invoice_number}`}
                        className="btn-invoice"
                      >
                        📄 Download Invoice
                      </Link>
                    </div>
                  )}

                  {/* Actions */}
                  <div className="order-actions">
                    <button 
                      className="btn-secondary"
                      onClick={() => window.print()}
                    >
                      🖨️ Print Details
                    </button>
                  </div>
                </div>
              </div>
            )}

            {/* Help Card */}
            <div className="help-card">
              <h3>Need Help?</h3>
              <p>If you have questions about your booking, please contact the property directly.</p>
              {booking.property.phone && (
                <a href={`tel:${booking.property.phone}`} className="help-link">
                  📞 Call Property
                </a>
              )}
              {booking.property.email && (
                <a href={`mailto:${booking.property.email}`} className="help-link">
                  📧 Email Property
                </a>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default BookingDetailPage;
