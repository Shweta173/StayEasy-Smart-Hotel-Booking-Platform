import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getPropertyDetails, checkAvailability } from '../services/api';
import { useAuth } from '../contexts/AuthContext';

// Add modal styles
const modalStyles = `
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.2s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .modal-content.auth-modal {
    background: white;
    border-radius: 12px;
    max-width: 480px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .modal-header {
    padding: 32px 24px 20px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
  }

  .modal-header h2 {
    margin: 0;
    font-size: 24px;
    color: #262626;
    font-weight: 700;
  }

  .btn-modal-primary:hover {
    background: linear-gradient(135deg, #00509e 0%, #003580 100%) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 53, 128, 0.3);
  }

  .btn-modal-secondary:hover {
    background: #f0f7ff !important;
    border-color: #003580 !important;
    color: #003580 !important;
  }
`;

/**
 * Room Selection Page (Page 4)
 * Shows available rooms with date picker and live pricing
 * Only bookable rooms are shown based on availability
 */
const RoomSelectionPage = () => {
  const { propertyId } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [property, setProperty] = useState(null);
  const [availableRooms, setAvailableRooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [checkingAvailability, setCheckingAvailability] = useState(false);
  const [error, setError] = useState('');

  // Date selection state
  const [dates, setDates] = useState({
    checkIn: '',
    checkOut: '',
    guests: 2,
    rooms: 1
  });

  const [nights, setNights] = useState(0);
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [pendingBooking, setPendingBooking] = useState(null);

  useEffect(() => {
    loadPropertyDetails();
    setDefaultDates();
  }, [propertyId]);

  useEffect(() => {
    if (dates.checkIn && dates.checkOut) {
      calculateNights();
      if (property) {
        checkRoomAvailability();
      }
    }
  }, [dates.checkIn, dates.checkOut, property]);

  const setDefaultDates = () => {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dayAfter = new Date(tomorrow);
    dayAfter.setDate(dayAfter.getDate() + 1);

    setDates(prev => ({
      ...prev,
      checkIn: tomorrow.toISOString().split('T')[0],
      checkOut: dayAfter.toISOString().split('T')[0]
    }));
  };

  const calculateNights = () => {
    if (dates.checkIn && dates.checkOut) {
      const checkIn = new Date(dates.checkIn);
      const checkOut = new Date(dates.checkOut);
      const diffTime = Math.abs(checkOut - checkIn);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      setNights(diffDays);
    }
  };

  const loadPropertyDetails = async () => {
    try {
      setLoading(true);
      const response = await getPropertyDetails(propertyId);
      if (response.success) {
        setProperty(response.property);
      } else {
        setError('Property not found');
      }
    } catch (err) {
      console.error('Error loading property:', err);
      setError('Failed to load property');
    } finally {
      setLoading(false);
    }
  };

  const checkRoomAvailability = async () => {
    try {
      setCheckingAvailability(true);
      setError('');

      const response = await checkAvailability({
        property_id: propertyId,
        check_in: dates.checkIn,
        check_out: dates.checkOut,
        guests: dates.guests,
        num_rooms: dates.rooms
      });

      if (response.success) {
        // Map backend response to frontend format
        const mappedRooms = (response.available_rooms || []).map(room => ({
          id: room.room_type_id,
          name: room.name,
          description: room.description,
          max_occupancy: room.max_occupancy,
          available_count: room.available_units,
          min_nights: room.min_nights,
          min_nights_error: room.min_nights_error,
          is_bookable: room.is_bookable,
          not_bookable_reason: room.not_bookable_reason,
          amenities: room.amenities?.map(a => a.name) || [],
          price: parseFloat(room.pricing.subtotal.replace(/,/g, '')) / room.pricing.nights,
          total_price: parseFloat(room.pricing.grand_total.replace(/,/g, '')),
          nights: room.pricing.nights,
          pricing_details: {
            ...room.pricing,
            subtotal: parseFloat(room.pricing.subtotal.replace(/,/g, '')),
            tax_total: parseFloat(room.pricing.tax_total.replace(/,/g, '')),
            service_fee: parseFloat(room.pricing.service_fee.replace(/,/g, '')),
            grand_total: parseFloat(room.pricing.grand_total.replace(/,/g, ''))
          }
        }));

        setAvailableRooms(mappedRooms);
        
        if (mappedRooms.length === 0) {
          setError('No rooms available for selected dates. Please try different dates.');
        }
      } else {
        setError(response.message || 'Failed to check availability');
        setAvailableRooms([]);
      }
    } catch (err) {
      console.error('Error checking availability:', err);
      setError('Failed to check availability. Please try again.');
      setAvailableRooms([]);
    } finally {
      setCheckingAvailability(false);
    }
  };

  const handleDateChange = (field, value) => {
    setDates(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleBookRoom = (room) => {
    // Check if room is bookable (meets minimum nights and other requirements)
    if (!room.is_bookable) {
      setError(room.not_bookable_reason || 'This room cannot be booked for the selected dates');
      return;
    }
    
    // Check if user is authenticated
    if (!isAuthenticated) {
      // Store the pending booking data
      const bookingIntent = {
        propertyId: property.id,
        propertyName: property.name,
        roomTypeId: room.id,
        roomTypeName: room.name,
        checkIn: dates.checkIn,
        checkOut: dates.checkOut,
        guests: dates.guests,
        rooms: dates.rooms,
        nights: nights,
        pricePerNight: room.price,
        totalPrice: room.total_price,
        maxOccupancy: room.max_occupancy
      };
      
      setPendingBooking(bookingIntent);
      setShowAuthModal(true);
      return;
    }
    
    // Calculate guests per room
    const guestsPerRoom = Math.ceil(dates.guests / dates.rooms);
    
    // Validate guest count per room against room max occupancy
    if (guestsPerRoom > room.max_occupancy) {
      setError(`Each room can accommodate a maximum of ${room.max_occupancy} guests. With ${dates.rooms} room(s) and ${dates.guests} guest(s), you need ${guestsPerRoom} guests per room. Please adjust the number of rooms or guests.`);
      return;
    }

    // Navigate to checkout with room and date details
    const bookingData = {
      propertyId: property.id,
      propertyName: property.name,
      roomTypeId: room.id,
      roomTypeName: room.name,
      checkIn: dates.checkIn,
      checkOut: dates.checkOut,
      guests: dates.guests,
      rooms: dates.rooms,
      nights: nights,
      pricePerNight: room.price,
      totalPrice: room.total_price,
      maxOccupancy: room.max_occupancy
    };

    localStorage.setItem('booking_data', JSON.stringify(bookingData));
    navigate('/checkout');
  };

  const handleAuthModalConfirm = () => {
    // Store booking intent for after login
    if (pendingBooking) {
      localStorage.setItem('booking_intent', JSON.stringify(pendingBooking));
      localStorage.setItem('redirect_after_login', `/room-selection/${propertyId}`);
    }
    
    setShowAuthModal(false);
    // Redirect to login page
    navigate('/login?redirect=' + encodeURIComponent('/checkout'));
  };

  const handleAuthModalCancel = () => {
    setShowAuthModal(false);
    setPendingBooking(null);
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0
    }).format(amount);
  };

  if (loading) {
    return (
      <div className="page-loading">
        <div className="spinner-large"></div>
        <p>Loading room options...</p>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="page-error">
        <h2>Property Not Found</h2>
        <button onClick={() => navigate('/')} className="btn-primary">Back to Home</button>
      </div>
    );
  }

  return (
    <div className="room-selection-wrapper">
      {/* Inject modal styles */}
      <style>{modalStyles}</style>
      
      {/* Header */}
      <div className="room-selection-header">
        <div className="container-wide">
          <button onClick={() => navigate(`/property/${propertyId}`)} className="btn-back-simple">
            ← Back to property
          </button>
          <h1 className="room-selection-title">Select your room at {property.name}</h1>
          {property.city && property.state && property.city !== 'Unknown City' && property.state !== 'Unknown State' && (
            <p className="room-selection-subtitle">{property.city}, {property.state}</p>
          )}
          {property.location && (!property.city || property.city === 'Unknown City') && (
            <p className="room-selection-subtitle">{property.location}</p>
          )}
        </div>
      </div>

      {/* Date Selector */}
      <div className="date-selector-section">
        <div className="container-wide">
          <div className="date-selector-card">
            <h3>Select your dates</h3>
            <div className="date-inputs-grid">
              <div className="date-input-group">
                <label>Check-in</label>
                <input
                  type="date"
                  value={dates.checkIn}
                  onChange={(e) => handleDateChange('checkIn', e.target.value)}
                  min={new Date().toISOString().split('T')[0]}
                  className="date-input-field"
                />
              </div>

              <div className="date-input-group">
                <label>Check-out</label>
                <input
                  type="date"
                  value={dates.checkOut}
                  onChange={(e) => handleDateChange('checkOut', e.target.value)}
                  min={dates.checkIn || new Date().toISOString().split('T')[0]}
                  className="date-input-field"
                />
              </div>

              <div className="date-input-group">
                <label>Rooms</label>
                <select
                  value={dates.rooms}
                  onChange={(e) => handleDateChange('rooms', parseInt(e.target.value))}
                  className="date-input-field"
                >
                  {[1, 2, 3, 4, 5].map(num => (
                    <option key={num} value={num}>{num} {num === 1 ? 'room' : 'rooms'}</option>
                  ))}
                </select>
              </div>

              <div className="date-input-group">
                <label>Guests</label>
                <select
                  value={dates.guests}
                  onChange={(e) => handleDateChange('guests', parseInt(e.target.value))}
                  className="date-input-field"
                >
                  {(() => {
                    // Calculate max guests based on available rooms and selected room count
                    const maxOccupancyPerRoom = availableRooms.length > 0 
                      ? Math.max(...availableRooms.map(r => r.max_occupancy))
                      : 6;
                    const maxGuestsTotal = maxOccupancyPerRoom * dates.rooms;
                    const options = [];
                    for (let i = 1; i <= Math.min(maxGuestsTotal, 20); i++) {
                      options.push(
                        <option key={i} value={i}>{i} {i === 1 ? 'guest' : 'guests'}</option>
                      );
                    }
                    return options;
                  })()}
                </select>
                {(() => {
                  // Show validation warning if guests exceed capacity
                  const maxOccupancyPerRoom = availableRooms.length > 0 
                    ? Math.max(...availableRooms.map(r => r.max_occupancy))
                    : 0;
                  const guestsPerRoom = Math.ceil(dates.guests / dates.rooms);
                  
                  if (maxOccupancyPerRoom > 0 && guestsPerRoom > maxOccupancyPerRoom) {
                    return (
                      <div style={{ 
                        color: '#d32f2f', 
                        fontSize: '13px', 
                        marginTop: '6px',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '4px'
                      }}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style={{ flexShrink: 0 }}>
                          <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                        <span>Maximum {maxOccupancyPerRoom} {maxOccupancyPerRoom === 1 ? 'guest' : 'guests'} per room. Please add more rooms.</span>
                      </div>
                    );
                  }
                  return null;
                })()}
              </div>

              <div className="date-input-group">
                <label>&nbsp;</label>
                <button 
                  onClick={checkRoomAvailability} 
                  className="btn-check-availability"
                  disabled={checkingAvailability || !dates.checkIn || !dates.checkOut}
                >
                  {checkingAvailability ? 'Checking...' : 'Check Availability'}
                </button>
              </div>
            </div>

            {nights > 0 && (
              <div className="nights-info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>{nights} {nights === 1 ? 'night' : 'nights'} stay</span>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Available Rooms */}
      <div className="available-rooms-section">
        <div className="container-wide">
          {error && (
            <div className="alert alert-warning">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
              </svg>
              <span>{error}</span>
            </div>
          )}

          {checkingAvailability ? (
            <div className="checking-availability">
              <div className="spinner-large"></div>
              <p>Checking room availability...</p>
            </div>
          ) : availableRooms.length > 0 ? (
            <>
              <h2 className="available-rooms-title">Available rooms ({availableRooms.length})</h2>
              
              {/* Show warning if requested rooms > available */}
              {dates.rooms > availableRooms.reduce((sum, room) => sum + (room.available_count || 0), 0) && (
                <div className="alert alert-warning">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                  </svg>
                  <span>
                    Not enough rooms available. You requested {dates.rooms} {dates.rooms === 1 ? 'room' : 'rooms'}, 
                    but only {availableRooms.reduce((sum, room) => sum + (room.available_count || 0), 0)} {availableRooms.reduce((sum, room) => sum + (room.available_count || 0), 0) === 1 ? 'room is' : 'rooms are'} available. 
                    Please reduce the number of rooms or try different dates.
                  </span>
                </div>
              )}
              
              <div className="rooms-list-selection">
                {availableRooms.map((room) => (
                  <div key={room.id} className="room-selection-card">
                    <div className="room-selection-image">
                      <img
                        src={'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'}
                        alt={room.name}
                      />
                      {room.available_count && room.available_count <= 3 && (
                        <div className="rooms-left-badge">
                          Only {room.available_count} left!
                        </div>
                      )}
                    </div>

                    <div className="room-selection-info">
                      <h3 className="room-selection-name">{room.name}</h3>
                      
                      <div className="room-features-list">
                        <div className="room-feature-item">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                          </svg>
                          <span>Max {room.max_occupancy} guests per room</span>
                        </div>
                        {room.min_nights && (
                          <div className="room-feature-item" style={{
                            color: room.is_bookable === false && room.min_nights_error ? '#d32f2f' : 'inherit',
                            fontWeight: room.is_bookable === false && room.min_nights_error ? '600' : 'normal'
                          }}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M19 4h-1V3a1 1 0 00-2 0v1H8V3a1 1 0 00-2 0v1H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm0 16H5V9h14v11z"/>
                            </svg>
                            <span>Minimum {room.min_nights} {room.min_nights === 1 ? 'night' : 'nights'} stay</span>
                            {room.is_bookable === false && room.min_nights_error && (
                              <span style={{
                                marginLeft: '8px',
                                padding: '2px 8px',
                                background: '#ffebee',
                                color: '#d32f2f',
                                borderRadius: '4px',
                                fontSize: '12px',
                                fontWeight: '600'
                              }}>
                                Required
                              </span>
                            )}
                          </div>
                        )}
                      </div>

                      {/* Minimum nights validation warning */}
                      {room.not_bookable_reason && room.is_bookable === false && (
                        <div className="alert alert-warning" style={{
                          marginTop: '10px', 
                          padding: '12px 16px', 
                          fontSize: '14px',
                          background: '#fff3cd',
                          border: '1px solid #ffc107',
                          borderRadius: '6px',
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px'
                        }}>
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style={{color: '#ff9800', flexShrink: 0}}>
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                          </svg>
                          <div>
                            <strong style={{display: 'block', marginBottom: '4px', color: '#856404'}}>Cannot book this room</strong>
                            <span style={{color: '#856404'}}>{room.not_bookable_reason}. You selected {nights} {nights === 1 ? 'night' : 'nights'}. Please select {room.min_nights || 2} or more nights.</span>
                          </div>
                        </div>
                      )}

                      {/* Guest occupancy validation warning */}
                      {(() => {
                        const guestsPerRoom = Math.ceil(dates.guests / dates.rooms);
                        return guestsPerRoom > room.max_occupancy && (
                          <div className="alert alert-warning" style={{marginTop: '10px', padding: '8px 12px', fontSize: '14px'}}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                            </svg>
                            <span>With {dates.guests} {dates.guests === 1 ? 'guest' : 'guests'} and {dates.rooms} {dates.rooms === 1 ? 'room' : 'rooms'}, you need {guestsPerRoom} guests per room. This room supports max {room.max_occupancy} per room. Please adjust guests or rooms.</span>
                          </div>
                        );
                      })()}

                      {room.amenities && room.amenities.length > 0 && (
                        <div className="room-amenities-list">
                          {room.amenities.slice(0, 5).map((amenity, idx) => (
                            <span key={idx} className="room-amenity-tag">
                              ✓ {amenity}
                            </span>
                          ))}
                        </div>
                      )}

                      {room.description && (
                        <p className="room-description-text">{room.description}</p>
                      )}
                    </div>

                    <div className="room-selection-pricing">
                      <div className="pricing-info">
                        <div className="price-per-night">
                          <span className="price-label">Price per night</span>
                          <span className="price-value">{formatCurrency(room.price)}</span>
                        </div>
                        
                        {nights > 0 && (
                          <>
                            <div className="total-price">
                              <span className="total-label">{nights} {nights === 1 ? 'night' : 'nights'} total</span>
                              <span className="total-value">{formatCurrency(room.total_price)}</span>
                            </div>
                            
                            {room.pricing_details && (
                              <div className="price-breakdown">
                                <small>Subtotal: {formatCurrency(parseFloat(room.pricing_details.subtotal))}</small>
                                <small>Tax: {formatCurrency(parseFloat(room.pricing_details.tax_total))}</small>
                              </div>
                            )}
                          </>
                        )}

                        <div className="price-includes">
                          <small>✓ All taxes and fees included</small>
                        </div>
                      </div>

                      <button
                        onClick={() => handleBookRoom(room)}
                        className="btn-book-room"
                        disabled={
                          !room.is_bookable ||
                          Math.ceil(dates.guests / dates.rooms) > room.max_occupancy
                        }
                      >
                        {!room.is_bookable && room.not_bookable_reason
                          ? room.not_bookable_reason
                          : (Math.ceil(dates.guests / dates.rooms) > room.max_occupancy
                              ? `Max ${room.max_occupancy} per room`
                              : "Book")}
                      </button>

                      <div className="cancellation-info">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <small>Free cancellation before 24 hours</small>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </>
          ) : dates.checkIn && dates.checkOut && !checkingAvailability ? (
            <div className="no-rooms-available">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1">
                <path d="M21 10.78V8c0-1.65-1.35-3-3-3h-4c-.77 0-1.47.3-2 .78-.53-.48-1.23-.78-2-.78H6C4.35 5 3 6.35 3 8v2.78c-.61.55-1 1.34-1 2.22v6h2v-2h16v2h2v-6c0-.88-.39-1.67-1-2.22z"/>
                <line x1="4" y1="4" x2="20" y2="20" strokeWidth="2"/>
              </svg>
              <h3>No rooms available</h3>
              <p>Unfortunately, there are no rooms available for your selected dates.</p>
              <p>Please try different dates or adjust the number of guests.</p>
            </div>
          ) : (
            <div className="select-dates-prompt">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 4h-1V3a1 1 0 00-2 0v1H8V3a1 1 0 00-2 0v1H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2zm0 16H5V9h14v11z"/>
              </svg>
              <h3>Select your dates</h3>
              <p>Choose check-in and check-out dates to see available rooms and prices.</p>
            </div>
          )}
        </div>
      </div>

      {/* Authentication Required Modal */}
      {showAuthModal && (
        <div className="modal-overlay" onClick={handleAuthModalCancel}>
          <div className="modal-content auth-modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{color: '#0071c2', marginBottom: '16px'}}>
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              <h2>Sign in required</h2>
              <p style={{color: '#6b6b6b', fontSize: '15px', marginTop: '8px'}}>
                You need to sign in or create an account to continue with your booking.
              </p>
            </div>
            
            <div className="modal-body" style={{padding: '0 24px 24px'}}>
              {pendingBooking && (
                <div style={{background: '#f5f5f5', padding: '16px', borderRadius: '8px', marginBottom: '20px'}}>
                  <p style={{fontSize: '14px', color: '#262626', marginBottom: '8px'}}>
                    <strong>{pendingBooking.roomTypeName}</strong>
                  </p>
                  <p style={{fontSize: '13px', color: '#6b6b6b', marginBottom: '4px'}}>
                    {pendingBooking.checkIn} → {pendingBooking.checkOut}
                  </p>
                  <p style={{fontSize: '13px', color: '#6b6b6b'}}>
                    {pendingBooking.guests} {pendingBooking.guests === 1 ? 'guest' : 'guests'} · {pendingBooking.rooms} {pendingBooking.rooms === 1 ? 'room' : 'rooms'} · {pendingBooking.nights} {pendingBooking.nights === 1 ? 'night' : 'nights'}
                  </p>
                </div>
              )}
              
              <button 
                onClick={handleAuthModalConfirm}
                className="btn-modal-primary"
                style={{
                  width: '100%',
                  padding: '14px 24px',
                  background: 'linear-gradient(135deg, #003580 0%, #00509e 100%)',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  fontSize: '16px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  marginBottom: '12px',
                  transition: 'all 0.2s ease'
                }}
              >
                Sign in / Register
              </button>
              
              <button 
                onClick={handleAuthModalCancel}
                className="btn-modal-secondary"
                style={{
                  width: '100%',
                  padding: '14px 24px',
                  background: 'white',
                  color: '#0071c2',
                  border: '2px solid #0071c2',
                  borderRadius: '6px',
                  fontSize: '16px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.2s ease'
                }}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default RoomSelectionPage;
