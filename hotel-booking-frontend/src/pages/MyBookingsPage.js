import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { getMyBookings } from '../services/api';
import './MyBookingsPage.css';

/**
 * Premium My Bookings Page (Page 8)
 * NOW WITH AUTHENTICATION REQUIRED
 * Only shows bookings for logged-in registered users
 */
function MyBookingsPage() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [bookingsData, setBookingsData] = useState(null);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [cancelBooking, setCancelBooking] = useState(null);
  const [cancelReason, setCancelReason] = useState('');
  const [successMessage, setSuccessMessage] = useState(null);

  // Load bookings on mount
  useEffect(() => {
    loadBookings();
  }, []);

  const loadBookings = async () => {
    try {
      setLoading(true);
      setError('');

      const response = await getMyBookings();
      
      if (response.success) {
        setBookingsData(response.data);
      } else {
        setError(response.message || 'Failed to load bookings');
      }
    } catch (err) {
      console.error('Load bookings error:', err);
      setError(err.message || 'Failed to load bookings');
    } finally {
      setLoading(false);
    }
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

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const getRatingStars = (rating = 5) => {
    return '★'.repeat(rating);
  };

  const BookingsTable = ({ bookings, title }) => (
    <div className="bookings-table-section">
      <h3 className="section-title">
        <span className="section-icon">{title === 'Upcoming' ? '' : ''}</span>
        {title} Bookings ({bookings.length})
      </h3>
      
      <div className="table-container">
        <table className="bookings-table">
          <thead>
            <tr>
              <th>Booking Ref</th>
              <th>Property</th>
              <th>Room Type</th>
              <th>Check-In</th>
              <th>Check-Out</th>
              <th>Guests</th>
              <th>Total</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {bookings.map(booking => (
              <tr key={booking.id}>
                <td className="booking-ref">{booking.booking_reference}</td>
                <td>
                  <div className="property-cell">
                    <strong>{booking.property.name}</strong>
                    <span className="location-text">
                      {booking.property.city}, {booking.property.country}
                    </span>
                  </div>
                </td>
                <td>{booking.room_type.name}</td>
                <td>{formatDate(booking.check_in)}</td>
                <td>{formatDate(booking.check_out)}</td>
                <td>{booking.guests} {booking.guests === 1 ? 'Adult' : 'Adults'}</td>
                <td className="amount-cell">{formatCurrency(booking.total_amount)}</td>
                <td>
                  <span className={`status-badge-table ${getStatusBadgeClass(booking.status)}`}>
                    {getStatusLabel(booking.status)}
                  </span>
                </td>
                <td>
                  <div className="action-buttons">
                    {booking.status === 'confirmed' && (
                      <button
                        onClick={() => handleCancelBooking(booking)}
                        className="btn-table btn-cancel"
                        title="Cancel Booking"
                      >
                        Cancel
                      </button>
                    )}
                    <button
                      onClick={() => setSelectedBooking(booking)}
                      className="btn-table btn-details"
                      title="View Details"
                    >
                      Details
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const handleCancelBooking = async (booking) => {
    setCancelBooking(booking);
    setCancelReason('');
  };

  const confirmCancelBooking = async () => {
    if (!cancelReason || cancelReason.trim() === '') {
      setSuccessMessage({
        type: 'error',
        title: 'Required Field',
        message: 'Please enter a reason for cancellation'
      });
      return;
    }

    try {
      const { API_BASE_URL } = await import('../config');
      const response = await fetch(`${API_BASE_URL.replace('/api', '')}/api/public/bookings/${cancelBooking.id}/cancel`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: JSON.stringify({ 
          reason: cancelReason,
          email: user.email
        })
      });

      const data = await response.json();

      if (data.success) {
        setCancelBooking(null);
        setCancelReason('');
        
        setSuccessMessage({
          type: 'success',
          title: 'Booking Cancelled Successfully!',
          message: 'Your booking has been cancelled successfully.'
        });
        
        // Refresh bookings
        loadBookings();
      } else {
        setSuccessMessage({
          type: 'error',
          title: 'Cancellation Failed',
          message: data.message || 'Failed to cancel booking. Please try again.'
        });
      }
    } catch (error) {
      console.error('Cancel error:', error);
      setSuccessMessage({
        type: 'error',
        title: 'Cancellation Failed',
        message: 'Failed to cancel booking. Please try again or contact support.'
      });
    }
  };

  if (loading) {
    return (
      <div className="premium-my-bookings">
        <div className="page-container">
          <div className="loading-state">
            <div className="spinner-large"></div>
            <p>Loading your bookings...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="premium-my-bookings">
        <div className="page-container">
          <div className="error-state">
            <div className="error-icon">⚠️</div>
            <h2>Error Loading Bookings</h2>
            <p>{error}</p>
            <button onClick={loadBookings} className="btn-retry">
              Try Again
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="premium-my-bookings">
      <div className="page-container">
        {/* Welcome Card */}
        <div className="welcome-card">
          <div className="welcome-left">
            <h2 className="welcome-title">
              
              Welcome back, {user?.first_name}!
            </h2>
            <p className="welcome-subtitle">Manage your upcoming and past reservations</p>
          </div>
          <div className="welcome-right">
            <div className="booking-count">
              <div className="count-number">{bookingsData?.upcoming?.length || 0}</div>
              <div className="count-label">Upcoming Bookings</div>
            </div>
          </div>
        </div>

        {/* Bookings Results */}
        {bookingsData && bookingsData.total === 0 ? (
          /* Empty State */
          <div className="empty-state">
            <div className="empty-illustration">📭</div>
            <h2 className="empty-title">No bookings yet</h2>
            <p className="empty-text">
              Start your journey by booking your first stay
            </p>
            <Link to="/search" className="btn-explore">
              Explore Properties
            </Link>
          </div>
        ) : (
          <>
            {/* Upcoming Bookings */}
            {bookingsData.upcoming.length > 0 && (
              <BookingsTable bookings={bookingsData.upcoming} title="Upcoming" />
            )}

            {/* Past Bookings */}
            {bookingsData.past.length > 0 && (
              <BookingsTable bookings={bookingsData.past} title="Past" />
            )}
          </>
        )}
      </div>

      {/* View Details Modal */}
      {selectedBooking && (
        <div className="modal-overlay" onClick={() => setSelectedBooking(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <button className="modal-close" onClick={() => setSelectedBooking(null)}>
              ✕
            </button>
            
            <h2 className="modal-title">Booking Details</h2>
            
            <div className="modal-body">
              {/* Booking Information */}
              <div className="modal-section">
                <h3 className="modal-section-title">Booking Information</h3>
                <div className="modal-detail-grid">
                  <div className="modal-detail">
                    <span className="modal-label">Booking Reference</span>
                    <p className="modal-value">{selectedBooking.booking_reference}</p>
                  </div>
                  <div className="modal-detail">
                    <span className="modal-label">Status</span>
                    <span className={`modal-badge ${getStatusBadgeClass(selectedBooking.status)}`}>
                      {getStatusLabel(selectedBooking.status)}
                    </span>
                  </div>
                </div>
              </div>

              {/* Property Information */}
              <div className="modal-section">
                <h3 className="modal-section-title">Property Information</h3>
                <div className="modal-detail">
                  <p className="modal-value">{selectedBooking.property.name}</p>
                  <p className="modal-location">
                    {selectedBooking.property.city}, {selectedBooking.property.state}, {selectedBooking.property.country}
                  </p>
                </div>
              </div>

              {/* Room & Stay Details */}
              <div className="modal-section">
                <h3 className="modal-section-title">Stay Details</h3>
                <div className="modal-detail-grid">
                  <div className="modal-detail">
                    <span className="modal-label">Room Type</span>
                    <p className="modal-value">{selectedBooking.room_type.name}</p>
                  </div>
                  <div className="modal-detail">
                    <span className="modal-label">Guests</span>
                    <p className="modal-value">{selectedBooking.guests} Adults</p>
                  </div>
                  <div className="modal-detail">
                    <span className="modal-label">Check-in</span>
                    <p className="modal-value">{formatDate(selectedBooking.check_in)}</p>
                  </div>
                  <div className="modal-detail">
                    <span className="modal-label">Check-out</span>
                    <p className="modal-value">{formatDate(selectedBooking.check_out)}</p>
                  </div>
                </div>
              </div>

              {/* Payment Information */}
              <div className="modal-section">
                <h3 className="modal-section-title">Payment Information</h3>
                <div className="modal-price">
                  <span>Total Amount Paid</span>
                  <span className="modal-price-value">{formatCurrency(selectedBooking.total_amount)}</span>
                </div>
                {selectedBooking.invoice_number && (
                  <div style={{ marginTop: '12px', fontSize: '14px', color: '#6B7280' }}>
                    Invoice Number: {selectedBooking.invoice_number}
                  </div>
                )}
              </div>
            </div>

            {/* Modal Actions */}
            <div className="modal-actions">
              {selectedBooking.invoice_number && (
                <Link
                  to={`/invoice/${selectedBooking.invoice_number}`}
                  className="btn-modal-primary"
                >
                  Download Invoice
                </Link>
              )}
              <button
                onClick={() => setSelectedBooking(null)}
                className="btn-modal-secondary"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Cancel Booking Modal */}
      {cancelBooking && (
        <div className="modal-overlay" onClick={() => setCancelBooking(null)}>
          <div className="modal-content cancel-modal" onClick={(e) => e.stopPropagation()}>
            <button className="modal-close" onClick={() => setCancelBooking(null)}>
              ✕
            </button>
            
            <h2 className="modal-title">Cancel Booking</h2>
            
            <div className="modal-body">
              <div className="cancel-warning">
                <div className="warning-icon">⚠️</div>
                <div className="warning-text">
                  <p className="warning-title">Are you sure you want to cancel this booking?</p>
                  <p className="warning-subtitle">
                    Booking Reference: <strong>{cancelBooking.booking_reference}</strong>
                  </p>
                  <p className="warning-subtitle">
                    Property: <strong>{cancelBooking.property.name}</strong>
                  </p>
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="cancelReason" className="form-label">
                  Reason for cancellation <span className="required">*</span>
                </label>
                <textarea
                  id="cancelReason"
                  className="form-textarea"
                  rows="4"
                  placeholder="Please enter the reason for cancellation..."
                  value={cancelReason}
                  onChange={(e) => setCancelReason(e.target.value)}
                  required
                />
              </div>
            </div>

            {/* Modal Actions */}
            <div className="modal-actions">
              <button
                onClick={confirmCancelBooking}
                className="btn-modal-danger"
              >
                Yes, Cancel Booking
              </button>
              <button
                onClick={() => {
                  setCancelBooking(null);
                  setCancelReason('');
                }}
                className="btn-modal-secondary"
              >
                No, Keep Booking
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Success/Error Message Modal */}
      {successMessage && (
        <div className="modal-overlay" onClick={() => setSuccessMessage(null)}>
          <div className="modal-content success-modal" onClick={(e) => e.stopPropagation()}>
            <button className="modal-close" onClick={() => setSuccessMessage(null)}>
              ✕
            </button>
            
            <div className="modal-body">
              <div className={`message-container ${successMessage.type}`}>
                <div className="message-icon">
                  {successMessage.type === 'success' ? '✅' : '❌'}
                </div>
                <h2 className="message-title">{successMessage.title}</h2>
                <p className="message-text">{successMessage.message}</p>
              </div>
            </div>

            <div className="modal-actions">
              <button
                onClick={() => setSuccessMessage(null)}
                className={successMessage.type === 'success' ? 'btn-modal-primary' : 'btn-modal-secondary'}
              >
                OK
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default MyBookingsPage;
