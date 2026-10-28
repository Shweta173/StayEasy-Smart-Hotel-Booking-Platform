import React from 'react';
import { useNavigate, Link } from 'react-router-dom';

/**
 * 404 Not Found Page
 * Shows when user navigates to an invalid URL
 */
const NotFoundPage = () => {
  const navigate = useNavigate();

  return (
    <>
      {/* Navigation Bar */}
      <header className="modern-navbar">
        <div className="navbar-container">
          {/* Logo */}
          <div className="navbar-logo">
            <Link to="/">Hotel Booking Platform</Link>
          </div>

          {/* Desktop Navigation Links */}
          <nav className="navbar-links">
            <Link to="/" className="nav-link">Home</Link>
            <Link to="/search" className="nav-link">Properties</Link>
          </nav>

          {/* Desktop Auth Buttons */}
          <div className="navbar-auth">
            <Link to="/login" className="btn-signin">Sign In</Link>
            <Link to="/register" className="btn-register">Register</Link>
          </div>
        </div>
      </header>

      <div className="not-found-page">
        <button className="back-button-404" onClick={() => navigate(-1)}>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Back
        </button>
        <div className="not-found-content">
          <h1 className="error-code">404</h1>
          <h2 className="error-title">Not Found</h2>
          <p className="error-description">
            The resource requested could not be found on this server!
          </p>
        </div>
      </div>
    </>
  );
};

export default NotFoundPage;
