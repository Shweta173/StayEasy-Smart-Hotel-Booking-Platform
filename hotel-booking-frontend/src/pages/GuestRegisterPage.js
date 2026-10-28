import { useState } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useAuth } from '../contexts/AuthContext';
import './AuthPage.css';

/**
 * Guest Registration Page (Separate)
 * Professional design inspired by Booking.com
 * NOW WITH AUTHENTICATION CONTEXT
 */
const GuestRegisterPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { register } = useAuth();
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [passwordError, setPasswordError] = useState('');
  const [confirmPasswordError, setConfirmPasswordError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
  });

  // Register form state
  const [registerData, setRegisterData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    country: ''
  });

  const handleRegisterChange = (e) => {
    const { name, value } = e.target;
    
    // Validate name fields - only allow letters and spaces
    if (name === 'first_name' || name === 'last_name') {
      const lettersOnly = /^[A-Za-z\s]*$/;
      if (!lettersOnly.test(value)) {
        return; // Don't update state if invalid characters
      }
    }
    
    // Validate phone field - only allow numbers and common phone formatting characters
    if (name === 'phone') {
      const phonePattern = /^[0-9+\-() ]*$/;
      if (!phonePattern.test(value)) {
        return; // Don't update state if invalid characters
      }
    }
    
    setRegisterData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear field error when user starts typing
    setFieldErrors(prev => ({
      ...prev,
      [name]: ''
    }));
    
    // Clear password errors when typing
    if (name === 'password') {
      setPasswordError('');
    }
    
    if (name === 'password_confirmation') {
      setConfirmPasswordError('');
    }
    
    setError('');
  };

  const handleBlur = (e) => {
    const { name, value } = e.target;
    
    // Validate on blur - show error if field is empty
    if (name === 'first_name' && value.trim() === '') {
      setFieldErrors(prev => ({...prev, first_name: 'This field is required'}));
    }
    
    // Last name is optional - no validation needed
    
    if (name === 'email') {
      if (value.trim() === '') {
        setFieldErrors(prev => ({...prev, email: 'This field is required'}));
      } else {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
          setFieldErrors(prev => ({...prev, email: 'Please enter a valid email address'}));
        }
      }
    }
    
    if (name === 'phone' && value.trim() === '') {
      setFieldErrors(prev => ({...prev, phone: 'This field is required'}));
    }
    
    if (name === 'password') {
      if (value.length === 0) {
        setPasswordError('This field is required');
      } else if (value.length < 8) {
        setPasswordError('Password must be at least 8 characters');
      }
    }
    
    if (name === 'password_confirmation') {
      if (value.length === 0) {
        setConfirmPasswordError('This field is required');
      } else if (value !== registerData.password) {
        setConfirmPasswordError('Passwords do not match');
      }
    }
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    // Validate all required fields and show ALL errors at once
    let hasErrors = false;
    const errors = {};

    // First Name validation (required)
    if (!registerData.first_name.trim()) {
      errors.first_name = 'This field is required';
      hasErrors = true;
    }

    // Last name is optional - no validation

    // Email validation (required)
    if (!registerData.email.trim()) {
      errors.email = 'This field is required';
      hasErrors = true;
    } else {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(registerData.email)) {
        errors.email = 'Please enter a valid email address';
        hasErrors = true;
      }
    }

    // Phone validation (required)
    if (!registerData.phone.trim()) {
      errors.phone = 'This field is required';
      hasErrors = true;
    }

    // Password validation (required)
    if (!registerData.password) {
      setPasswordError('This field is required');
      hasErrors = true;
    } else if (registerData.password.length < 8) {
      setPasswordError('Password must be at least 8 characters');
      hasErrors = true;
    } else {
      setPasswordError('');
    }

    // Confirm Password validation (required)
    if (!registerData.password_confirmation) {
      setConfirmPasswordError('This field is required');
      hasErrors = true;
    } else if (registerData.password !== registerData.password_confirmation) {
      setConfirmPasswordError('Passwords do not match');
      hasErrors = true;
    } else {
      setConfirmPasswordError('');
    }

    // Update all field errors at once
    setFieldErrors(errors);

    // If there are errors, stop submission - NO TOAST, only inline errors
    if (hasErrors) {
      setLoading(false);
      return;
    }

    try {
      const result = await register(registerData);
      
      if (result.success) {
        // Show success toast only
        toast.success('Registration successful! Welcome to our platform.', {
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
        // Handle backend validation errors - show inline, not toast
        const errorMessage = result.message || 'Registration failed. Please try again.';
        
        // Check if it's an email error (common backend validation)
        if (errorMessage.toLowerCase().includes('email')) {
          setFieldErrors(prev => ({...prev, email: errorMessage}));
        } else if (errorMessage.toLowerCase().includes('phone')) {
          setFieldErrors(prev => ({...prev, phone: errorMessage}));
        } else {
          // Generic error - show under email field
          setFieldErrors(prev => ({...prev, email: errorMessage}));
        }
      }
    } catch (err) {
      // Handle network/server errors - show inline
      const errorMessage = err.message || 'Registration failed. Please try again.';
      setFieldErrors(prev => ({...prev, email: errorMessage}));
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
            <h1>Create your account</h1>
            
          </div>

          {/* Register Form */}
          <form onSubmit={handleRegister} className="auth-form" noValidate>
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="first_name">First name</label>
                <input
                  id="first_name"
                  type="text"
                  name="first_name"
                  className="form-control"
                  value={registerData.first_name}
                  onChange={handleRegisterChange}
                  onBlur={handleBlur}
                  placeholder="Enter your first name"
                  autoComplete="given-name"
                />
                {fieldErrors.first_name && <small className="form-error">{fieldErrors.first_name}</small>}
              </div>

              <div className="form-group">
                <label htmlFor="last_name">Last name (Optional)</label>
                <input
                  id="last_name"
                  type="text"
                  name="last_name"
                  className="form-control"
                  value={registerData.last_name}
                  onChange={handleRegisterChange}
                  onBlur={handleBlur}
                  placeholder="Enter your last name"
                  autoComplete="family-name"
                />
                {fieldErrors.last_name && <small className="form-error">{fieldErrors.last_name}</small>}
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="register-email">Email address</label>
              <input
                id="register-email"
                type="email"
                name="email"
                className="form-control"
                value={registerData.email}
                onChange={handleRegisterChange}
                onBlur={handleBlur}
                placeholder="Enter your email address"
                autoComplete="email"
              />
              {fieldErrors.email && <small className="form-error">{fieldErrors.email}</small>}
            </div>

            <div className="form-group">
              <label htmlFor="phone">Phone number</label>
              <input
                id="phone"
                type="tel"
                name="phone"
                className="form-control"
                value={registerData.phone}
                onChange={handleRegisterChange}
                onBlur={handleBlur}
                placeholder="+1 (555) 000-0000"
                autoComplete="tel"
              />
              {fieldErrors.phone && <small className="form-error">{fieldErrors.phone}</small>}
            </div>

            <div className="form-group">
              <label htmlFor="country">Country/Region (Optional)</label>
              <input
                id="country"
                type="text"
                name="country"
                className="form-control"
                value={registerData.country}
                onChange={handleRegisterChange}
                placeholder="United States"
                autoComplete="country"
              />
            </div>

            <div className="form-group">
              <label htmlFor="register-password">Password</label>
              <div className="password-input-wrapper">
                <input
                  id="register-password"
                  type={showPassword ? "text" : "password"}
                  name="password"
                  className="form-control"
                  value={registerData.password}
                  onChange={handleRegisterChange}
                  onBlur={handleBlur}
                  placeholder="Create a password (min. 8 characters)"
                  minLength="8"
                  autoComplete="new-password"
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
                      <path fillRule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a 1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clipRule="evenodd"/>
                      <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                    </svg>
                  )}
                </button>
              </div>
              {passwordError && <small className="form-error">{passwordError}</small>}
              {!passwordError && <small className="form-hint">Use at least 8 characters</small>}
            </div>

            <div className="form-group">
              <label htmlFor="password_confirmation">Confirm password</label>
              <div className="password-input-wrapper">
                <input
                  id="password_confirmation"
                  type={showConfirmPassword ? "text" : "password"}
                  name="password_confirmation"
                  className="form-control"
                  value={registerData.password_confirmation}
                  onChange={handleRegisterChange}
                  onBlur={handleBlur}
                  placeholder="Confirm your password"
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  className="password-toggle-btn"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  aria-label={showConfirmPassword ? "Hide password" : "Show password"}
                >
                  {showConfirmPassword ? (
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
              {confirmPasswordError && <small className="form-error">{confirmPasswordError}</small>}
            </div>

            <button
              type="submit"
              className="btn-auth-primary"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="spinner-small"></span>
                  Creating account...
                </>
              ) : (
                'Create Account'
              )}
            </button>

            <div className="auth-footer">
              <p>
                Already have an account? {' '}
                <Link to="/login" className="link-button">
                  Sign in
                </Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default GuestRegisterPage;
