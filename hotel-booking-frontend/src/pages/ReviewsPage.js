import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { API_BASE_URL } from '../config';
import './ReviewsPage.css';

const ReviewsPage = () => {
  const [properties, setProperties] = useState([]);
  const [selectedProperty, setSelectedProperty] = useState('');
  const [rating, setRating] = useState(5);
  const [reviewText, setReviewText] = useState('');
  const [guestName, setGuestName] = useState('');
  const [guestEmail, setGuestEmail] = useState('');
  const [loading, setLoading] = useState(false);
  
  // Inline validation errors
  const [validationErrors, setValidationErrors] = useState({});
  
  // Toast notifications
  const [toasts, setToasts] = useState([]);

  useEffect(() => {
    fetchProperties();
  }, []);

  // Toast notification system
  const showToast = (message, type = 'success', title = '') => {
    const id = Date.now();
    const newToast = { id, message, type, title };
    setToasts(prev => [...prev, newToast]);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
      removeToast(id);
    }, 4000);
  };

  const removeToast = (id) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  };

  const fetchProperties = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/public/properties/search`);
      // Handle both possible response structures
      const propertiesData = response.data.properties || response.data.data || response.data || [];
      console.log('Properties response:', response.data);
      
      // Ensure it's an array
      if (Array.isArray(propertiesData)) {
        setProperties(propertiesData);
      } else if (propertiesData.data && Array.isArray(propertiesData.data)) {
        setProperties(propertiesData.data);
      } else {
        console.warn('Unexpected properties format:', propertiesData);
        setProperties([]);
      }
    } catch (error) {
      console.error('Failed to fetch properties:', error);
      setProperties([]);
    }
  };

  // Inline validation functions
  const validateField = (name, value) => {
    const errors = { ...validationErrors };

    switch (name) {
      case 'selectedProperty':
        if (!value) {
          errors.selectedProperty = 'Please select a property';
        } else {
          delete errors.selectedProperty;
        }
        break;
      
      case 'guestName':
        if (!value.trim()) {
          errors.guestName = 'Name is required';
        } else if (value.trim().length < 2) {
          errors.guestName = 'Name must be at least 2 characters';
        } else {
          delete errors.guestName;
        }
        break;
      
      case 'guestEmail':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value.trim()) {
          errors.guestEmail = 'Email is required';
        } else if (!emailRegex.test(value)) {
          errors.guestEmail = 'Please enter a valid email';
        } else {
          delete errors.guestEmail;
        }
        break;
      
      case 'reviewText':
        if (!value.trim()) {
          errors.reviewText = 'Review is required';
        } else if (value.trim().length < 10) {
          errors.reviewText = `Review must be at least 10 characters (${value.trim().length}/10)`;
        } else if (value.length > 1000) {
          errors.reviewText = 'Review must be less than 1000 characters';
        } else {
          delete errors.reviewText;
        }
        break;
      
      default:
        break;
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleFieldChange = (name, value) => {
    // Update state
    switch (name) {
      case 'selectedProperty':
        setSelectedProperty(value);
        break;
      case 'guestName':
        setGuestName(value);
        break;
      case 'guestEmail':
        setGuestEmail(value);
        break;
      case 'reviewText':
        setReviewText(value);
        break;
      default:
        break;
    }
    
    // Validate on change
    validateField(name, value);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validate all fields
    const isPropertyValid = validateField('selectedProperty', selectedProperty);
    const isNameValid = validateField('guestName', guestName);
    const isEmailValid = validateField('guestEmail', guestEmail);
    const isReviewValid = validateField('reviewText', reviewText);

    if (!isPropertyValid || !isNameValid || !isEmailValid || !isReviewValid) {
      showToast('Please fix all validation errors before submitting', 'warning', 'Validation Error');
      return;
    }

    setLoading(true);

    try {
      console.log('Submitting review:', {
        property_id: selectedProperty,
        guest_name: guestName,
        guest_email: guestEmail,
        rating: rating,
        comment: reviewText
      });

      const response = await axios.post(`${API_BASE_URL}/public/reviews`, {
        property_id: selectedProperty,
        guest_name: guestName,
        guest_email: guestEmail,
        rating: rating,
        comment: reviewText
      });

      console.log('Review response:', response.data);

      showToast(
        'Your review will be visible after admin approval.',
        'success',
        'Review Submitted Successfully!'
      );
      
      // Reset form
      setSelectedProperty('');
      setRating(5);
      setReviewText('');
      setGuestName('');
      setGuestEmail('');
      setValidationErrors({});
    } catch (error) {
      console.error('Review submission error:', error);
      console.error('Error response:', error.response?.data);
      showToast(
        error.response?.data?.message || 'Failed to submit review. Please try again.',
        'error',
        'Submission Failed'
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="reviews-page">
      {/* Toast Container */}
      <div className="toast-container">
        {toasts.map(toast => (
          <div key={toast.id} className={`toast ${toast.type}`}>
            <div className="toast-icon">
              {toast.type === 'success' && '✓'}
              {toast.type === 'error' && '✗'}
              {toast.type === 'warning' && '⚠'}
              {toast.type === 'info' && 'ℹ'}
            </div>
            <div className="toast-content">
              <div className="toast-title">{toast.title || (toast.type === 'success' ? 'Success' : toast.type === 'error' ? 'Error' : 'Info')}</div>
              <div className="toast-message">{toast.message}</div>
            </div>
            <button className="toast-close" onClick={() => removeToast(toast.id)}>×</button>
          </div>
        ))}
      </div>

      <div className="container">
        <div className="reviews-header">
          <h1>Write a Review</h1>
          <p>Share your experience and help other travelers</p>
        </div>

        <div className="review-form-container">
          <form onSubmit={handleSubmit} className="review-form">
            
            {/* Property Selection */}
            <div className="form-group">
              <label htmlFor="property">Select Property *</label>
              <select
                id="property"
                value={selectedProperty}
                onChange={(e) => handleFieldChange('selectedProperty', e.target.value)}
                onBlur={(e) => validateField('selectedProperty', e.target.value)}
                required
                className={`form-select ${validationErrors.selectedProperty ? 'error' : ''}`}
              >
                <option value="">Choose a property...</option>
                {Array.isArray(properties) && properties.length > 0 ? (
                  properties.map(property => (
                    <option key={property.id} value={property.id}>
                      {property.name} - {property.location || property.city}
                    </option>
                  ))
                ) : (
                  <option disabled>Loading properties...</option>
                )}
              </select>
              {validationErrors.selectedProperty && (
                <span className="error-message-inline">{validationErrors.selectedProperty}</span>
              )}
            </div>

            {/* Guest Name */}
            <div className="form-group">
              <label htmlFor="guestName">Your Name *</label>
              <input
                type="text"
                id="guestName"
                value={guestName}
                onChange={(e) => handleFieldChange('guestName', e.target.value)}
                onBlur={(e) => validateField('guestName', e.target.value)}
                required
                placeholder="Enter your name"
                className={`form-input ${validationErrors.guestName ? 'error' : ''}`}
              />
              {validationErrors.guestName && (
                <span className="error-message-inline">{validationErrors.guestName}</span>
              )}
            </div>

            {/* Guest Email */}
            <div className="form-group">
              <label htmlFor="guestEmail">Your Email *</label>
              <input
                type="email"
                id="guestEmail"
                value={guestEmail}
                onChange={(e) => handleFieldChange('guestEmail', e.target.value)}
                onBlur={(e) => validateField('guestEmail', e.target.value)}
                required
                placeholder="Enter your email"
                className={`form-input ${validationErrors.guestEmail ? 'error' : ''}`}
              />
              {validationErrors.guestEmail && (
                <span className="error-message-inline">{validationErrors.guestEmail}</span>
              )}
            </div>

            {/* Rating */}
            <div className="form-group">
              <label>Rating *</label>
              <div className="star-rating">
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    type="button"
                    className={`star ${rating >= star ? 'active' : ''}`}
                    onClick={() => setRating(star)}
                  >
                    ★
                  </button>
                ))}
                <span className="rating-text">{rating} out of 5</span>
              </div>
            </div>

            {/* Review Text */}
            <div className="form-group">
              <label htmlFor="reviewText">Your Review *</label>
              <textarea
                id="reviewText"
                value={reviewText}
                onChange={(e) => handleFieldChange('reviewText', e.target.value)}
                onBlur={(e) => validateField('reviewText', e.target.value)}
                required
                placeholder="Tell us about your experience..."
                rows="6"
                className={`form-textarea ${validationErrors.reviewText ? 'error' : ''}`}
              />
              {validationErrors.reviewText ? (
                <span className="error-message-inline">{validationErrors.reviewText}</span>
              ) : (
                <small className="form-hint">
                  Minimum 10 characters. Be specific and honest. ({reviewText.length}/1000)
                </small>
              )}
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              disabled={loading}
              className="btn-submit"
            >
              {loading ? 'Submitting...' : 'Submit Review'}
            </button>

            <p className="form-note">
              * Your review will be reviewed by our team before being published.
            </p>
          </form>

          {/* Info Sidebar */}
          <div className="review-info">
            <h3>Review Guidelines</h3>
            <ul>
              <li>✓ Be honest and detailed</li>
              <li>✓ Focus on your experience</li>
              <li>✓ Mention specific aspects (service, cleanliness, location)</li>
              <li>✗ No offensive language</li>
              <li>✗ No personal attacks</li>
              <li>✗ No spam or fake reviews</li>
            </ul>

            <div className="review-stats">
              <h4>Why Review?</h4>
              <p>Your reviews help:</p>
              <ul>
                <li>Other travelers make informed decisions</li>
                <li>Hotels improve their services</li>
                <li>Build a trusted community</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReviewsPage;
