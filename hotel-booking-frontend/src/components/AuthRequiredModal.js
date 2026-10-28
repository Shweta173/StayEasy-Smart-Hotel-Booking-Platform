import React from 'react';
import { useNavigate } from 'react-router-dom';
import './AuthRequiredModal.css';

/**
 * Authentication Required Modal
 * Shows when user tries to book without being logged in
 */
const AuthRequiredModal = ({ onClose }) => {
  const navigate = useNavigate();

  const handleSignIn = () => {
    navigate('/login', { 
      state: { 
        returnTo: '/checkout',
        message: 'Please sign in to complete your booking.' 
      } 
    });
  };

  const handleRegister = () => {
    navigate('/register', { 
      state: { 
        returnTo: '/checkout',
        message: 'Create an account to complete your booking.' 
      } 
    });
  };

  return (
    <div className="auth-modal-overlay" onClick={onClose}>
      <div className="auth-modal-content" onClick={(e) => e.stopPropagation()}>
        <button className="auth-modal-close" onClick={onClose}>
          ×
        </button>
        
        <div className="auth-modal-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
            <path d="M12 8v4M12 16h.01"/>
          </svg>
        </div>

        <h2 className="auth-modal-title">Sign In Required</h2>
        
        <p className="auth-modal-message">
          You must sign in or register to complete your booking.
        </p>

        <div className="auth-modal-actions">
          <button 
            className="auth-modal-btn auth-modal-btn-primary" 
            onClick={handleSignIn}
          >
            Sign In
          </button>
          
          <button 
            className="auth-modal-btn auth-modal-btn-secondary" 
            onClick={handleRegister}
          >
            Create Account
          </button>
        </div>

        <button 
          className="auth-modal-btn auth-modal-btn-link" 
          onClick={onClose}
        >
          Cancel
        </button>
      </div>
    </div>
  );
};

export default AuthRequiredModal;
