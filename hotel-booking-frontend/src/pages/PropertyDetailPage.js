import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getPropertyDetails } from '../services/api';
import { STORAGE_URL } from '../config';
import ReviewsModal from '../components/ReviewsModal';
import ImageLightbox from '../components/ImageLightbox';
import { useAuth } from '../contexts/AuthContext';

/**
 * Simple Error Page Component (used when property not found)
 */
const PropertyNotFoundPage = () => {
  const navigate = useNavigate();

  return (
    <div className="simple-error-page">
      <button className="back-button-404" onClick={() => navigate(-1)}>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
      </button>
      <h2 className="simple-error-title">Property Not Found</h2>
    </div>
  );
};

/**
 * Property Detail Page (Page 3)
 * Professional Booking.com-inspired design
 * Shows: Gallery, description, amenities, rooms, reviews, map
 */
const PropertyDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');
  const [showAllPhotos, setShowAllPhotos] = useState(false);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const [isReviewsModalOpen, setIsReviewsModalOpen] = useState(false);
  const [isFavorited, setIsFavorited] = useState(false);
  const [showMap, setShowMap] = useState(false);
  const [showReviewModal, setShowReviewModal] = useState(false);
  const [reviewData, setReviewData] = useState({
    guest_name: '',
    guest_email: '',
    rating: 5,
    comment: ''
  });
  const [reviewSubmitting, setReviewSubmitting] = useState(false);
  const [reviewSuccess, setReviewSuccess] = useState(false);
  const [reviewErrors, setReviewErrors] = useState({
    guest_name: '',
    guest_email: '',
    comment: ''
  });
  const [touchedFields, setTouchedFields] = useState({
    guest_name: false,
    guest_email: false,
    comment: false
  });

  useEffect(() => {
    loadPropertyDetails();
  }, [id]);

  const loadPropertyDetails = async () => {
    try {
      setLoading(true);
      const response = await getPropertyDetails(id);
      if (response.success) {
        setProperty(response.property);
      } else {
        setError('Property not found');
      }
    } catch (err) {
      console.error('Error loading property:', err);
      setError('Failed to load property details');
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const handleFavoriteToggle = () => {
    setIsFavorited(!isFavorited);
    // TODO: Add to favorites in backend/localStorage
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: property.name,
        text: `Check out ${property.name} in ${property.city}`,
        url: window.location.href,
      }).catch(err => console.log('Error sharing:', err));
    } else {
      // Fallback: Copy to clipboard
      navigator.clipboard.writeText(window.location.href);
      alert('Link copied to clipboard!');
    }
  };

  const handleReserveClick = () => {
    navigate(`/rooms/${property.id}`);
  };

  const handleShowMap = () => {
    setShowMap(!showMap);
  };

  // Handle opening review modal with autofill if user is logged in
  const handleOpenReviewModal = () => {
    if (isAuthenticated && user) {
      // Autofill name and email from logged-in user
      setReviewData(prev => ({
        ...prev,
        guest_name: `${user.first_name} ${user.last_name}`,
        guest_email: user.email
      }));
    } else {
      // Reset to empty for non-authenticated users
      setReviewData({
        guest_name: '',
        guest_email: '',
        rating: 5,
        comment: ''
      });
    }
    setShowReviewModal(true);
  };

  const handleReviewChange = (e) => {
    const { name, value } = e.target;
    setReviewData(prev => ({ ...prev, [name]: value }));
    
    // Validate field on change
    if (touchedFields[name]) {
      validateField(name, value);
    }
  };

  const handleFieldBlur = (fieldName) => {
    setTouchedFields(prev => ({ ...prev, [fieldName]: true }));
    validateField(fieldName, reviewData[fieldName]);
  };

  const validateField = (fieldName, value) => {
    let error = '';
    
    switch(fieldName) {
      case 'guest_name':
        if (!value.trim()) {
          error = 'Name is required';
        } else if (value.trim().length < 2) {
          error = 'Name must be at least 2 characters';
        }
        break;
      
      case 'guest_email':
        if (!value.trim()) {
          error = 'Email is required';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          error = 'Please enter a valid email address';
        }
        break;
      
      case 'comment':
        if (!value.trim()) {
          error = 'Review comment is required';
        } else if (value.trim().length < 10) {
          error = 'Review must be at least 10 characters';
        }
        break;
      
      default:
        break;
    }
    
    setReviewErrors(prev => ({ ...prev, [fieldName]: error }));
    return error === '';
  };

  const validateAllFields = () => {
    const isNameValid = validateField('guest_name', reviewData.guest_name);
    const isEmailValid = validateField('guest_email', reviewData.guest_email);
    const isCommentValid = validateField('comment', reviewData.comment);
    
    setTouchedFields({
      guest_name: true,
      guest_email: true,
      comment: true
    });
    
    return isNameValid && isEmailValid && isCommentValid;
  };

  const handleSubmitReview = async (e) => {
    e.preventDefault();
    
    // Validate all fields before submission
    if (!validateAllFields()) {
      return;
    }
    
    setReviewSubmitting(true);

    try {
      // Prepare review payload
      const reviewPayload = {
        ...reviewData,
        property_id: property.id
      };

      // If user is authenticated, include guest_id
      if (isAuthenticated && user) {
        reviewPayload.guest_id = user.id;
      }

      const response = await fetch('http://localhost:8000/api/public/reviews', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(reviewPayload)
      });

      const data = await response.json();

      if (data.success) {
        setReviewSuccess(true);
        setReviewData({
          guest_name: '',
          guest_email: '',
          rating: 5,
          comment: ''
        });
        setReviewErrors({
          guest_name: '',
          guest_email: '',
          comment: ''
        });
        setTouchedFields({
          guest_name: false,
          guest_email: false,
          comment: false
        });
        setTimeout(() => {
          setShowReviewModal(false);
          setReviewSuccess(false);
        }, 3000);
      } else {
        alert(data.message || 'Failed to submit review');
      }
    } catch (error) {
      console.error('Error submitting review:', error);
      alert('Failed to submit review. Please try again.');
    } finally {
      setReviewSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="page-loading">
        <div className="spinner-large"></div>
        <p>Loading property...</p>
      </div>
    );
  }

  if (error || !property) {
    return <PropertyNotFoundPage />;
  }

  // Get property images from backend ONLY
  const getImageUrl = (img) => {
    if (!img) return null;
    // If already a full URL, return as-is
    if (img.startsWith('http')) return img;
    // If relative path starting with /storage, prepend backend URL
    if (img.startsWith('/storage/')) return `http://127.0.0.1:8000${img}`;
    // Otherwise prepend backend URL
    return `http://127.0.0.1:8000${img}`;
  };

  // Use ONLY backend images - no placeholders on detail page
  const images = property.images && property.images.length > 0
    ? property.images.map(img => getImageUrl(img.url)).filter(url => url !== null)
    : [];

  // Log if no images uploaded in backend
  if (images.length === 0) {
    console.warn('No images found for property:', property.name, '- Gallery will be hidden');
  }

  // Extract amenities from room types (these come from backend)
  const allAmenities = new Set();
  property.room_types?.forEach(room => {
    room.amenities?.forEach(amenity => allAmenities.add(amenity));
  });

  // Use actual room types from backend - NO MOCK DATA
  const rooms = property.room_types || [];

  return (
    <div className="property-detail-wrapper">
      {/* Property Header */}
      <div className="property-header-section">
        <div className="container-wide">
          <div className="property-header-content">
            <div className="property-title-area">
              <div className="property-stars">
                {'⭐'.repeat(Math.floor(property.average_rating || 4))}
              </div>
              <h1 className="property-title">{property.name}</h1>
              <p className="property-address">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                </svg>
                {property.address}, {property.city}, {property.state}, {property.country}
              </p>
            </div>

            <div className="property-actions">
              <button 
                className={`btn-icon-action ${isFavorited ? 'favorited' : ''}`}
                onClick={handleFavoriteToggle}
                title={isFavorited ? 'Remove from favorites' : 'Add to favorites'}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill={isFavorited ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="2">
                  <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
              </button>
              <button 
                className="btn-icon-action"
                onClick={handleShare}
                title="Share this property"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <circle cx="18" cy="5" r="3"/>
                  <circle cx="6" cy="12" r="3"/>
                  <circle cx="18" cy="19" r="3"/>
                  <path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/>
                </svg>
              </button>
              <button 
                className="btn-reserve-main"
                onClick={handleReserveClick}
              >
                Reserve
              </button>
            </div>
          </div>

          {property.reviews && property.reviews.length > 0 && (
            <div className="property-rating-summary">
              <div className="rating-box">
                <div className="rating-score-large">{property.average_rating.toFixed(1)}</div>
                <div className="rating-info">
                  <div className="rating-label-large">
                    {property.average_rating >= 4.5 ? 'Excellent' : 
                     property.average_rating >= 4.0 ? 'Very good' : 
                     property.average_rating >= 3.0 ? 'Good' : 
                     property.average_rating >= 2.0 ? 'Fair' : 'Poor'}
                  </div>
                  <div className="rating-count-large">
                    {property.total_reviews || property.review_count || property.reviews.length} {(property.total_reviews || property.review_count || property.reviews.length) === 1 ? 'review' : 'reviews'}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Photo Gallery - ONLY show if images exist in backend */}
      {images.length > 0 && (
        <div className="photo-gallery-section">
          <div className="container-wide">
            <div className="gallery-grid">
              <div className="gallery-main" onClick={() => { setLightboxOpen(true); setLightboxIndex(0); }}>
                <img src={images[0]} alt={property.name} />
              </div>
              {images.length > 1 && (
                <div className="gallery-thumbnails">
                  {images.slice(1, 5).map((img, idx) => (
                    <div key={idx} className="gallery-thumb" onClick={() => { setLightboxOpen(true); setLightboxIndex(idx + 1); }}>
                      <img src={img} alt={`${property.name} ${idx + 2}`} />
                      {idx === 3 && images.length > 5 && (
                        <div className="gallery-overlay">
                          <span>+{images.length - 5} photos</span>
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Image Lightbox */}
      {lightboxOpen && property && (
        <ImageLightbox
          images={property.images}
          initialIndex={lightboxIndex}
          onClose={() => setLightboxOpen(false)}
        />
      )}

      {/* Map Section - Show when toggled */}
      {showMap && (
        <div className="map-section">
          <div className="container-wide">
            <div className="map-container">
              <iframe
                src={`https://www.openstreetmap.org/export/embed.html?bbox=${property.longitude - 0.01},${property.latitude - 0.01},${property.longitude + 0.01},${property.latitude + 0.01}&layer=mapnik&marker=${property.latitude},${property.longitude}`}
                width="100%"
                height="450"
                style={{ border: 0, borderRadius: '8px' }}
                allowFullScreen=""
                loading="lazy"
                title="Property Location"
              ></iframe>
              <p className="map-caption">
                📍 {property.address}, {property.city}, {property.state}, {property.country}
              </p>
              <a 
                href={`https://www.google.com/maps/search/?api=1&query=${property.latitude},${property.longitude}`}
                target="_blank"
                rel="noopener noreferrer"
                className="view-on-google-maps"
              >
                View on Google Maps →
              </a>
            </div>
          </div>
        </div>
      )}

      {/* Tabs Navigation */}
      <div className="property-tabs-section">
        <div className="container-wide">
          <nav className="property-tabs">
            <button 
              className={`tab-link ${activeTab === 'overview' ? 'active' : ''}`}
              onClick={() => setActiveTab('overview')}
            >
              Overview
            </button>
            <button 
              className={`tab-link ${activeTab === 'prices' ? 'active' : ''}`}
              onClick={() => setActiveTab('prices')}
            >
              Prices
            </button>
            <button 
              className={`tab-link ${activeTab === 'facilities' ? 'active' : ''}`}
              onClick={() => setActiveTab('facilities')}
            >
              Facilities
            </button>
            <button 
              className={`tab-link ${activeTab === 'reviews' ? 'active' : ''}`}
              onClick={() => setActiveTab('reviews')}
            >
              Guest reviews ({property.total_reviews || property.review_count || property.reviews?.length || 0})
            </button>
          </nav>
        </div>
      </div>

      {/* Main Content */}
      <div className="property-content-section">
        <div className="container-wide">
          <div className="property-layout">
            {/* Left Content */}
            <div className="property-main-content">
              
              {/* OVERVIEW TAB - Show Everything */}
              {activeTab === 'overview' && (
                <>
                  {/* Description */}
                  <section className="content-section">
                    <h2>Property Description</h2>
                    {property.description ? (
                      <p className="property-description">{property.description}</p>
                    ) : (
                      <p className="property-description" style={{color: '#999', fontStyle: 'italic'}}>
                        No description available. Please add a description in the admin panel.
                      </p>
                    )}
                  </section>

                  {/* Amenities */}
                  {allAmenities.size > 0 && (
                    <section className="content-section">
                      <h2>Property Amenities</h2>
                      <div className="facilities-grid">
                        {Array.from(allAmenities).map((amenity, idx) => (
                          <div key={idx} className="facility-item">
                            <span className="facility-icon">✓</span>
                            <span className="facility-name">{amenity}</span>
                          </div>
                        ))}
                      </div>
                    </section>
                  )}

                  {/* Available Rooms with Prices */}
                  <section className="content-section">
                    <h2>Available Rooms</h2>
                    {rooms.length > 0 ? (
                      <div className="rooms-list">
                        {rooms.map(room => (
                          <div key={room.id} className="room-card">
                            <div className="room-info">
                              <h3 className="room-name">{room.name}</h3>
                              {room.description && (
                                <p className="room-description">{room.description}</p>
                              )}
                              <div className="room-details">
                                <span>👥 Max {room.max_occupancy} guests</span>
                              </div>
                              {room.amenities && room.amenities.length > 0 && (
                                <div className="room-amenities">
                                  {room.amenities.map((amenity, idx) => (
                                    <span key={idx} className="room-amenity">✓ {amenity}</span>
                                  ))}
                                </div>
                              )}
                            </div>
                            <div className="room-booking">
                              <div className="room-price">
                                <div className="price-amount">{formatCurrency(parseFloat(room.base_price))}</div>
                                <div className="price-period">per night</div>
                              </div>
                              <button 
                                className="btn-select-room"
                                onClick={() => navigate(`/rooms/${property.id}`)}
                              >
                                Select room
                              </button>
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div className="no-rooms-message">
                        <p style={{color: '#999', fontStyle: 'italic'}}>
                          No rooms available. Please add room types in the admin panel.
                        </p>
                      </div>
                    )}
                  </section>

                  {/* Guest Reviews */}
                  {property.reviews && property.reviews.length > 0 && (
                    <section className="content-section">
                      <div className="section-header-with-button">
                        <h2>Guest Reviews</h2>
                        <button 
                          className="btn-view-all-reviews"
                          onClick={() => setIsReviewsModalOpen(true)}
                        >
                          View All {property.reviews.length} Reviews →
                        </button>
                      </div>
                      <div className="reviews-list">
                        {property.reviews.slice(0, 3).map(review => (
                          <div key={review.id} className="guest-review">
                            <div className="review-header">
                              <div className="reviewer-info">
                                <div className="reviewer-avatar">
                                  {review.guest_name ? review.guest_name[0].toUpperCase() : '👤'}
                                </div>
                                <div>
                                  <div className="reviewer-name">{review.guest_name}</div>
                                  <div className="reviewer-date">{review.created_at}</div>
                                </div>
                              </div>
                              <div className="review-rating">⭐ {review.rating.toFixed(1)}</div>
                            </div>
                            <div className="review-content">
                              <p>"{review.comment}"</p>
                              {review.staff_reply && (
                                <div className="staff-reply">
                                  <strong>Staff Response:</strong>
                                  <p>{review.staff_reply}</p>
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                      {property.reviews.length > 3 && (
                        <button 
                          className="btn-load-more-reviews"
                          onClick={() => setIsReviewsModalOpen(true)}
                        >
                          Show all {property.reviews.length} reviews
                        </button>
                      )}
                    </section>
                  )}
                </>
              )}

              {/* PRICES TAB - Show Available Rooms */}
              {activeTab === 'prices' && (
                <section className="content-section">
                  <h2>Available Rooms</h2>
                  {rooms.length > 0 ? (
                    <div className="rooms-list">
                      {rooms.map(room => (
                        <div key={room.id} className="room-card">
                          <div className="room-info">
                            <h3 className="room-name">{room.name}</h3>
                            {room.description && (
                              <p className="room-description">{room.description}</p>
                            )}
                            <div className="room-details">
                              <span>👥 Max {room.max_occupancy} guests</span>
                            </div>
                            {room.amenities && room.amenities.length > 0 && (
                              <div className="room-amenities">
                                {room.amenities.map((amenity, idx) => (
                                  <span key={idx} className="room-amenity">✓ {amenity}</span>
                                ))}
                              </div>
                            )}
                          </div>
                          <div className="room-booking">
                            <div className="room-price">
                              <div className="price-amount">{formatCurrency(parseFloat(room.base_price))}</div>
                              <div className="price-period">per night</div>
                            </div>
                            <button 
                              className="btn-select-room"
                              onClick={() => navigate(`/rooms/${property.id}`)}
                            >
                              Select room
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="no-rooms-message">
                      <p style={{color: '#999', fontStyle: 'italic'}}>
                        No rooms available. Please add room types in the admin panel.
                      </p>
                    </div>
                  )}
                </section>
              )}

              {/* FACILITIES TAB - Show Amenities Only */}
              {activeTab === 'facilities' && (
                <section className="content-section">
                  <h2>Property Amenities</h2>
                  {allAmenities.size > 0 ? (
                    <div className="facilities-grid">
                      {Array.from(allAmenities).map((amenity, idx) => (
                        <div key={idx} className="facility-item">
                          <span className="facility-icon">✓</span>
                          <span className="facility-name">{amenity}</span>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p style={{color: '#999', fontStyle: 'italic'}}>
                      No amenities available. Please add amenities to room types in the admin panel.
                    </p>
                  )}
                </section>
              )}

              {/* REVIEWS TAB - Show Guest Reviews */}
              {activeTab === 'reviews' && (
                <section className="content-section">
                  <div className="section-header-with-button">
                    <h2>Guest Reviews</h2>
                    <button 
                      className="btn-write-review"
                      onClick={handleOpenReviewModal}
                    >
                      Write a Review
                    </button>
                  </div>
                  {property.reviews && property.reviews.length > 0 ? (
                    <>
                      <div className="reviews-list">
                        {property.reviews.map(review => (
                          <div key={review.id} className="guest-review">
                            <div className="review-header">
                              <div className="reviewer-info">
                                <div className="reviewer-avatar">
                                  {review.guest_name ? review.guest_name[0].toUpperCase() : '👤'}
                                </div>
                                <div>
                                  <div className="reviewer-name">{review.guest_name}</div>
                                  <div className="reviewer-date">{review.created_at}</div>
                                </div>
                              </div>
                              <div className="review-rating">⭐ {review.rating.toFixed(1)}</div>
                            </div>
                            <div className="review-content">
                              <p>"{review.comment}"</p>
                              {review.staff_reply && (
                                <div className="staff-reply">
                                  <strong>Staff Response:</strong>
                                  <p>{review.staff_reply}</p>
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </>
                  ) : (
                    <div className="no-reviews-message">
                      <p style={{color: '#999', fontStyle: 'italic'}}>
                        No reviews yet. Be the first to review this property!
                      </p>
                    </div>
                  )}
                </section>
              )}
              
            </div>

            {/* Right Sidebar */}
            <aside className="property-sidebar">
              {/* Property Highlights */}
              <div className="sidebar-card">
                <h3>Property highlights</h3>
                <div className="highlight-item">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                  </svg>
                  <div>
                    <strong>Situated in the real heart of {property.city || 'the city'}</strong>
                    <p>This hotel has an excellent location score of {property.average_rating?.toFixed(1) || 'N/A'}</p>
                  </div>
                </div>
              </div>

              {/* Location Map Card */}
              <div className="sidebar-card">
                <h3>Great location - show map</h3>
                <div className="sidebar-map-container">
                  {property.latitude && property.longitude ? (
                    <iframe
                      src={`https://www.openstreetmap.org/export/embed.html?bbox=${property.longitude - 0.01},${property.latitude - 0.01},${property.longitude + 0.01},${property.latitude + 0.01}&layer=mapnik&marker=${property.latitude},${property.longitude}`}
                      width="100%"
                      height="200"
                      style={{ border: 0, borderRadius: '4px' }}
                      allowFullScreen=""
                      loading="lazy"
                      title="Property Location"
                    ></iframe>
                  ) : (
                    <div className="map-placeholder">
                      <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style={{opacity: 0.3}}>
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                      </svg>
                      <p style={{margin: '8px 0 0 0', fontSize: '13px', color: '#999'}}>Map not available</p>
                    </div>
                  )}
                </div>
                <div className="sidebar-location-info">
                  <p className="location-address">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style={{flexShrink: 0}}>
                      <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                    </svg>
                    <span>{property.address}, {property.city}, {property.state}, {property.country}</span>
                  </p>
                  {property.latitude && property.longitude && (
                    <a 
                      href={`https://www.google.com/maps/search/?api=1&query=${property.latitude},${property.longitude}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="view-on-map-link"
                    >
                      View on Google Maps →
                    </a>
                  )}
                </div>
              </div>

              {/* Reserve Button */}
              <div className="sidebar-sticky">
                <button 
                  className="btn-reserve-sidebar"
                  onClick={handleReserveClick}
                >
                  Reserve
                </button>
              </div>
            </aside>
          </div>
        </div>
      </div>

      {/* Reviews Modal */}
      <ReviewsModal 
        isOpen={isReviewsModalOpen}
        onClose={() => setIsReviewsModalOpen(false)}
        reviews={property.reviews || []}
        propertyName={property.name}
      />

      {/* Write Review Modal */}
      {showReviewModal && (
        <div className="modal-overlay">
          <div className="modal-content review-modal">
            <button className="modal-close" onClick={() => setShowReviewModal(false)}>
              ✕
            </button>
            
            <h2 className="modal-title">Write a Review</h2>
            <p className="modal-subtitle">Share your experience at {property.name}</p>
            
            {reviewSuccess ? (
              <div className="success-message">
                <div className="success-icon">✓</div>
                <h3>Thank you for your review!</h3>
              </div>
            ) : (
              <form onSubmit={handleSubmitReview} className="review-form">
                <div className="form-group">
                  <label htmlFor="guest_name">Your Name *</label>
                  <input
                    type="text"
                    id="guest_name"
                    name="guest_name"
                    value={reviewData.guest_name}
                    onChange={handleReviewChange}
                    onBlur={() => handleFieldBlur('guest_name')}
                    placeholder="Enter your name"
                    className={`form-control ${touchedFields.guest_name && reviewErrors.guest_name ? 'error' : ''}`}
                    readOnly={isAuthenticated}
                    style={isAuthenticated ? { backgroundColor: '#f5f5f5', cursor: 'not-allowed' } : {}}
                  />
                  {touchedFields.guest_name && reviewErrors.guest_name && (
                    <span className="error-message">{reviewErrors.guest_name}</span>
                  )}
                  {isAuthenticated && (
                    <small style={{ color: '#666', fontSize: '0.85rem' }}>
                      Using your account information
                    </small>
                  )}
                </div>

                <div className="form-group">
                  <label htmlFor="guest_email">Your Email *</label>
                  <input
                    type="email"
                    id="guest_email"
                    name="guest_email"
                    value={reviewData.guest_email}
                    onChange={handleReviewChange}
                    onBlur={() => handleFieldBlur('guest_email')}
                    placeholder="Enter your email"
                    className={`form-control ${touchedFields.guest_email && reviewErrors.guest_email ? 'error' : ''}`}
                    readOnly={isAuthenticated}
                    style={isAuthenticated ? { backgroundColor: '#f5f5f5', cursor: 'not-allowed' } : {}}
                  />
                  {touchedFields.guest_email && reviewErrors.guest_email && (
                    <span className="error-message">{reviewErrors.guest_email}</span>
                  )}
                  {isAuthenticated && (
                    <small style={{ color: '#666', fontSize: '0.85rem' }}>
                      Using your account information
                    </small>
                  )}
                </div>

                <div className="form-group">
                  <label htmlFor="rating">Rating *</label>
                  <select
                    id="rating"
                    name="rating"
                    value={reviewData.rating}
                    onChange={handleReviewChange}
                    className="form-control"
                  >
                    <option value="5">⭐⭐⭐⭐⭐ Excellent (5)</option>
                    <option value="4">⭐⭐⭐⭐ Very Good (4)</option>
                    <option value="3">⭐⭐⭐ Good (3)</option>
                    <option value="2">⭐⭐ Fair (2)</option>
                    <option value="1">⭐ Poor (1)</option>
                  </select>
                </div>

                <div className="form-group">
                  <label htmlFor="comment">Your Review *</label>
                  <textarea
                    id="comment"
                    name="comment"
                    value={reviewData.comment}
                    onChange={handleReviewChange}
                    onBlur={() => handleFieldBlur('comment')}
                    placeholder="Share your experience at this property..."
                    rows="5"
                    className={`form-control ${touchedFields.comment && reviewErrors.comment ? 'error' : ''}`}
                  />
                  {touchedFields.comment && reviewErrors.comment && (
                    <span className="error-message">{reviewErrors.comment}</span>
                  )}
                </div>

                <div className="form-actions">
                  <button 
                    type="button" 
                    className="btn-secondary"
                    onClick={() => setShowReviewModal(false)}
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit" 
                    className="btn-primary"
                    disabled={reviewSubmitting}
                  >
                    {reviewSubmitting ? 'Submitting...' : 'Submit Review'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}

      {/* Full Photo Gallery Modal */}
      {showAllPhotos && (
        <div className="photo-modal-overlay" onClick={() => setShowAllPhotos(false)}>
          <div className="photo-modal-content" onClick={(e) => e.stopPropagation()}>
            <button className="photo-modal-close" onClick={() => setShowAllPhotos(false)}>
              ✕
            </button>
            <div className="photo-modal-grid">
              {images.map((img, idx) => (
                <div key={idx} className="photo-modal-item">
                  <img src={img} alt={`${property.name} ${idx + 1}`} />
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PropertyDetailPage;
