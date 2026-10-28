import React, { useState, useEffect } from 'react';
import './ImageLightbox.css';

const ImageLightbox = ({ images, initialIndex = 0, onClose }) => {
  const [currentIndex, setCurrentIndex] = useState(initialIndex);

  // Handle keyboard navigation
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === 'Escape') onClose();
      if (e.key === 'ArrowLeft') goToPrevious();
      if (e.key === 'ArrowRight') goToNext();
    };

    document.addEventListener('keydown', handleKeyDown);
    document.body.style.overflow = 'hidden'; // Prevent body scroll

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'unset';
    };
  }, [currentIndex]);

  const goToNext = () => {
    setCurrentIndex((prev) => (prev + 1) % images.length);
  };

  const goToPrevious = () => {
    setCurrentIndex((prev) => (prev - 1 + images.length) % images.length);
  };

  const goToImage = (index) => {
    setCurrentIndex(index);
  };

  if (!images || images.length === 0) return null;

  return (
    <div className="lightbox-overlay" onClick={onClose}>
      <div className="lightbox-container" onClick={(e) => e.stopPropagation()}>
        {/* Close Button */}
        <button className="lightbox-close" onClick={onClose} title="Close (Esc)">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>

        {/* Navigation Buttons */}
        {images.length > 1 && (
          <>
            <button className="lightbox-nav lightbox-prev" onClick={goToPrevious} title="Previous (←)">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M15 18l-6-6 6-6" />
              </svg>
            </button>
            <button className="lightbox-nav lightbox-next" onClick={goToNext} title="Next (→)">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </button>
          </>
        )}

        {/* Main Image */}
        <div className="lightbox-image-wrapper">
          <img
            src={`http://localhost:8000${images[currentIndex].url}`}
            alt={`Property image ${currentIndex + 1}`}
            className="lightbox-image"
          />
          <div className="lightbox-counter">
            {currentIndex + 1} / {images.length}
          </div>
        </div>

        {/* Thumbnails */}
        {images.length > 1 && (
          <div className="lightbox-thumbnails">
            {images.map((image, index) => (
              <div
                key={image.id || index}
                className={`lightbox-thumbnail ${index === currentIndex ? 'active' : ''}`}
                onClick={() => goToImage(index)}
              >
                <img
                  src={`http://localhost:8000${image.url}`}
                  alt={`Thumbnail ${index + 1}`}
                />
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default ImageLightbox;
