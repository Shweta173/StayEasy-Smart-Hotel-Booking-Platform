import React, { useEffect } from 'react';
import './ReviewsModal.css';

const ReviewsModal = ({ isOpen, onClose, reviews, propertyName }) => {
  // Close on ESC key
  useEffect(() => {
    const handleEsc = (e) => {
      if (e.key === 'Escape') onClose();
    };
    if (isOpen) {
      document.addEventListener('keydown', handleEsc);
      document.body.style.overflow = 'hidden'; // Prevent background scroll
    }
    return () => {
      document.removeEventListener('keydown', handleEsc);
      document.body.style.overflow = 'unset';
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const averageRating = reviews.length > 0
    ? (reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length).toFixed(1)
    : 0;

  const ratingDistribution = [5, 4, 3, 2, 1].map(rating => {
    const count = reviews.filter(r => r.rating === rating).length;
    const percentage = reviews.length > 0 ? (count / reviews.length) * 100 : 0;
    return { rating, count, percentage };
  });

  return (
    <>
      {/* Backdrop */}
      <div className="reviews-modal-backdrop" onClick={onClose}></div>

      {/* Modal Sliding from Right */}
      <div className="reviews-modal">
        {/* Header */}
        <div className="reviews-modal-header">
          <div>
            <h2>Guest Reviews</h2>
            <p className="property-name">{propertyName}</p>
          </div>
          <button className="close-btn" onClick={onClose}>✕</button>
        </div>

        {/* Rating Summary */}
        <div className="reviews-summary">
          <div className="average-rating">
            <div className="rating-number">{averageRating}</div>
            <div className="rating-info">
              <div className="stars">
                {[1, 2, 3, 4, 5].map(star => (
                  <span key={star} className={star <= averageRating ? 'star filled' : 'star'}>
                    ★
                  </span>
                ))}
              </div>
              <p>{reviews.length} review{reviews.length !== 1 ? 's' : ''}</p>
            </div>
          </div>

          {/* Rating Bars */}
          <div className="rating-bars">
            {ratingDistribution.map(({ rating, count, percentage }) => (
              <div key={rating} className="rating-bar-row">
                <span className="rating-label">{rating}★</span>
                <div className="bar-container">
                  <div className="bar-fill" style={{ width: `${percentage}%` }}></div>
                </div>
                <span className="rating-count">{count}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Reviews List */}
        <div className="reviews-list">
          {reviews.length > 0 ? (
            reviews.map((review) => (
              <div key={review.id} className="review-card">
                <div className="review-header">
                  <div className="reviewer-info">
                    <div className="reviewer-avatar">
                      {review.guest_name ? review.guest_name[0].toUpperCase() : 'G'}
                    </div>
                    <div>
                      <h4 className="reviewer-name">{review.guest_name || 'Anonymous'}</h4>
                      <p className="review-date">
                        {new Date(review.created_at).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric'
                        })}
                      </p>
                    </div>
                  </div>
                  <div className="review-rating">
                    {[1, 2, 3, 4, 5].map(star => (
                      <span
                        key={star}
                        className={star <= review.rating ? 'star filled' : 'star'}
                      >
                        ★
                      </span>
                    ))}
                  </div>
                </div>
                <p className="review-comment">{review.comment}</p>
              </div>
            ))
          ) : (
            <div className="no-reviews">
              <p>No reviews yet. Be the first to review!</p>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default ReviewsModal;
