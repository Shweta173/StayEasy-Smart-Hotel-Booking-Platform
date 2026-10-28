import { useState } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useAuth } from '../contexts/AuthContext';
import './AuthPage.css';

/**
 * Guest Login Page (Separate)
 * Professional design inspired by Booking.com
 * NOW WITH AUTHENTICATION CONTEXT
 */
const GuestLoginPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [fieldErrors, setFieldErrors] = useState({
    email: '',
    password: ''
  });

  // Login form state
  const [loginData, setLoginData] = useState({
    email: '',
    password: ''
  });

  const handleLoginChange = (e) => {
    setLoginData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
    
    // Clear field error when user starts typing
    setFieldErrors(prev => ({
      ...prev,
      [e.target.name]: ''
    }));
    
    setError('');
  };

  const handleBlur = (e) => {
    const { name, value } = e.target;
    
    if (name === 'email' && !value.trim()) {
      setFieldErrors(prev => ({...prev, email: 'This field is required'}));
    } else if (name === 'email' && value.trim()) {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(value)) {
        setFieldErrors(prev => ({...prev, email: 'Please enter a valid email address'}));
      }
    }
    
    if (name === 'password' && !value) {
      setFieldErrors(prev => ({...prev, password: 'This field is required'}));
    }
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    
    // Clear previous field errors
    setFieldErrors({
      email: '',
      password: ''
    });

    // Validate all fields
    let hasErrors = false;
    const errors = {};

    if (!loginData.email.trim()) {
      errors.email = 'This field is required';
      hasErrors = true;
    } else {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(loginData.email)) {
        errors.email = 'Please enter a valid email address';
        hasErrors = true;
      }
    }

    if (!loginData.password) {
      errors.password = 'This field is required';
      hasErrors = true;
    }

    setFieldErrors(errors);

    if (hasErrors) {
      setLoading(false);
      return;
    }

    try {
      const result = await login(loginData.email, loginData.password);
      
      if (result.success) {
        // Show success toast
        toast.success('Login successful! Welcome back.', {
          position: "top-right",
          autoClose: 3000,
        });
        
        // Check for redirect parameter
        const urlParams = new URLSearchParams(location.search);
        const redirect = urlParams.get('redirect');
        
        // Decode and redirect to specified page or HOME PAGE
        const decodedRedirect = redirect ? decodeURIComponent(redirect) : '/';
        setTimeout(() => {
          navigate(decodedRedirect, { replace: true });
        }, 500);
      } else {
        // Show backend error as general message at top
        setError(result.message || 'Invalid email or password');
      }
    } catch (err) {
      // Show network error as general message at top
      setError(err.message || 'Login failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-container">
        <div className="auth-card">
          {/* Header */}
          <div className="auth-header">
            <h1>Sign in to your account</h1>
            
          </div>

          {/* Login Form */}
          <form onSubmit={handleLogin} className="auth-form" noValidate>
            {/* General error message at top */}
            {error && (
              <div style={{
                padding: '12px 16px',
                marginBottom: '20px',
                backgroundColor: '#fee2e2',
                border: '1px solid #fecaca',
                borderRadius: '6px',
                color: '#dc2626',
                fontSize: '14px',
                fontWeight: '500'
              }}>
                {error}
              </div>
            )}
            
            <div className="form-group">
              <label htmlFor="login-email">Email address</label>
              <input
                id="login-email"
                type="email"
                name="email"
                className="form-control"
                value={loginData.email}
                onChange={handleLoginChange}
                onBlur={handleBlur}
                placeholder="Enter your email address"
                autoComplete="email"
              />
              {fieldErrors.email && <small className="form-error">{fieldErrors.email}</small>}
            </div>

            <div className="form-group">
              <label htmlFor="login-password">Password</label>
              <div className="password-input-wrapper">
                <input
                  id="login-password"
                  type={showPassword ? "text" : "password"}
                  name="password"
                  className="form-control"
                  value={loginData.password}
                  onChange={handleLoginChange}
                  onBlur={handleBlur}
                  placeholder="Enter your password"
                  autoComplete="current-password"
                />
                <button
                  type="button"
                  className="password-toggle-btn"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label={showPassword ? "Hide password" : "Show password"}
                >
                  {showPassword ? (
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                      <path fillRule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clipRule="evenodd"/>
                    </svg>
                  ) : (
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clipRule="evenodd"/>
                      <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                    </svg>
                  )}
                </button>
              </div>
              {fieldErrors.password && <small className="form-error">{fieldErrors.password}</small>}
            </div>

            <button
              type="submit"
              className="btn-auth-primary"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="spinner-small"></span>
                  Signing in...
                </>
              ) : (
                'Sign In'
              )}
            </button>

            <div className="auth-footer">
              <p>
                Don't have an account? {' '}
                <Link to="/register" className="link-button">
                  Create one
                </Link>
              </p>
              <p className="text-muted">
                <Link to="/" className="link-button">
                  Continue as guest
                </Link>
              </p>
            </div>
          </form>

          {/* Footer */}
          <div className="auth-card-footer">
            <p className="copyright">All rights reserved.</p>
            <p className="copyright">Copyright © 2026 Hotel Booking Platform™</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default GuestLoginPage;
