import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { toast } from 'react-toastify';
import { guestLogin, guestRegister } from '../services/api';

/**
 * Page 7: Guest Login / Register
 * Professional design inspired by Booking.com
 */
const LoginPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  
  // Check URL parameter for mode (login or register)
  const urlParams = new URLSearchParams(location.search);
  const modeParam = urlParams.get('mode');
  
  const [isLogin, setIsLogin] = useState(modeParam !== 'register'); // Default to login unless mode=register
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Login form state
  const [loginData, setLoginData] = useState({
    email: '',
    password: ''
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

  useEffect(() => {
    // Check if already logged in
    const token = localStorage.getItem('guest_token');
    if (token) {
      const urlParams = new URLSearchParams(location.search);
      const redirect = urlParams.get('redirect');
      navigate(redirect || '/my-bookings');
    }
  }, [navigate, location]);

  const handleLoginChange = (e) => {
    setLoginData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
    setError('');
  };

  const handleRegisterChange = (e) => {
    setRegisterData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
    setError('');
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await guestLogin(loginData);
      
      if (response.success) {
        // Store token and guest data
        localStorage.setItem('guest_token', response.data.token);
        localStorage.setItem('guest_data', JSON.stringify(response.data.guest));
        
        // Show success toast
        toast.success('Login successful! Welcome back.', {
          position: "top-right",
          autoClose: 3000,
        });
        
        // Check for redirect parameter
        const urlParams = new URLSearchParams(location.search);
        const redirect = urlParams.get('redirect');
        
        // Redirect to specified page or My Bookings
        setTimeout(() => {
          navigate(redirect || '/my-bookings');
        }, 500);
      }
    } catch (err) {
      setError(err.message || 'Login failed. Please check your credentials.');
      toast.error(err.message || 'Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    // Validate passwords match
    if (registerData.password !== registerData.password_confirmation) {
      setError('Passwords do not match');
      setLoading(false);
      return;
    }

    // Validate password length
    if (registerData.password.length < 8) {
      setError('Password must be at least 8 characters');
      setLoading(false);
      return;
    }

    try {
      const response = await guestRegister(registerData);
      
      if (response.success) {
        // Store token and guest data
        localStorage.setItem('guest_token', response.data.token);
        localStorage.setItem('guest_data', JSON.stringify(response.data.guest));
        
        // Show success toast
        toast.success('Registration successful! Welcome to our platform.', {
          position: "top-right",
          autoClose: 3000,
        });
        
        // Check for redirect parameter
        const urlParams = new URLSearchParams(location.search);
        const redirect = urlParams.get('redirect');
        
        // Redirect to specified page or My Bookings
        setTimeout(() => {
          navigate(redirect || '/my-bookings');
        }, 500);
      }
    } catch (err) {
      setError(err.message || 'Registration failed. Please try again.');
      toast.error(err.message || 'Registration failed. Please try again.');
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
            <h1>{isLogin ? 'Sign in or create an account' : 'Create your account'}</h1>
            <p className="auth-subtitle">
              {isLogin 
                ? 'You can sign in using your account to access our services.' 
                : 'Create an account to track and manage your bookings easily.'}
            </p>
          </div>

          {/* Error Alert */}
          {error && (
            <div className="alert alert-danger">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd"/>
              </svg>
              <span>{error}</span>
            </div>
          )}

          {/* Login Form */}
          {isLogin ? (
            <form onSubmit={handleLogin} className="auth-form">
              <div className="form-group">
                <label htmlFor="login-email">Email address</label>
                <input
                  id="login-email"
                  type="email"
                  name="email"
                  className="form-control"
                  value={loginData.email}
                  onChange={handleLoginChange}
                  placeholder="Enter your email address"
                  required
                  autoComplete="email"
                />
              </div>

              <div className="form-group">
                <label htmlFor="login-password">Password</label>
                <input
                  id="login-password"
                  type="password"
                  name="password"
                  className="form-control"
                  value={loginData.password}
                  onChange={handleLoginChange}
                  placeholder="Enter your password"
                  required
                  autoComplete="current-password"
                />
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
                  'Continue with email'
                )}
              </button>

              <div className="auth-divider">
                <span>or use one of these options</span>
              </div>

              <div className="social-login-buttons">
                <button type="button" className="btn-social" disabled>
                  <svg width="18" height="18" viewBox="0 0 18 18">
                    <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                    <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.593.102-1.17.282-1.709V4.958H.957C.347 6.173 0 7.548 0 9c0 1.452.348 2.827.957 4.042l3.007-2.335z"/>
                    <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                  </svg>
                  <span>Google</span>
                </button>
                <button type="button" className="btn-social" disabled>
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                    <path d="M16.403 8.936c-.03-3.248-2.661-4.828-2.782-4.9 1.515-2.217.419-3.42.383-3.477-1.478-.123-3.26.872-4.095 1.968-.745.975-1.398 2.533-.998 4.028 1.678.131 3.388-1.037 4.49-1.619z"/>
                    <path d="M12.165 18c-1.342-.012-1.879-.944-3.506-.944-1.628 0-2.137.932-3.48.968-1.391.037-2.96-2.419-4.127-3.956C-.826 11.13-.214 7.44.993 5.488c.843-1.364 2.353-2.228 3.994-2.251 1.252-.023 2.433.842 3.198.842.765 0 2.198-1.041 3.707-.888.631.026 2.404.255 3.544 1.922-.092.057-2.117 1.237-2.09 3.688.026 2.926 2.569 3.906 2.598 3.918-.028.085-.406 1.389-1.338 2.751-.813 1.189-1.657 2.374-2.986 2.398-.555.012-1.455-.35-1.455-.778z"/>
                  </svg>
                  <span>Apple</span>
                </button>
                <button type="button" className="btn-social" disabled>
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="#1877F2">
                    <path d="M18 9c0-4.97-4.03-9-9-9S0 4.03 0 9c0 4.491 3.291 8.215 7.594 8.892V11.72H5.308V9h2.286V7.017c0-2.257 1.343-3.503 3.4-3.503.985 0 2.014.176 2.014.176v2.213h-1.135c-1.118 0-1.467.694-1.467 1.406V9h2.496l-.399 2.72h-2.097v6.172C14.709 17.215 18 13.491 18 9z"/>
                  </svg>
                  <span>Facebook</span>
                </button>
              </div>

              <div className="auth-footer">
                <p>
                  Don't have an account? {' '}
                  <button type="button" onClick={() => { setIsLogin(false); navigate('/login?mode=register'); }} className="link-button">
                    Create one
                  </button>
                </p>
                <p className="text-muted">
                  <button type="button" onClick={() => navigate('/')} className="link-button">
                    Continue as guest
                  </button>
                </p>
              </div>
            </form>
          ) : (
            /* Register Form */
            <form onSubmit={handleRegister} className="auth-form">
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
                    placeholder="Enter your first name"
                    required
                    autoComplete="given-name"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="last_name">Last name</label>
                  <input
                    id="last_name"
                    type="text"
                    name="last_name"
                    className="form-control"
                    value={registerData.last_name}
                    onChange={handleRegisterChange}
                    placeholder="Enter your last name"
                    required
                    autoComplete="family-name"
                  />
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
                  placeholder="Enter your email address"
                  required
                  autoComplete="email"
                />
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
                  placeholder="+1 (555) 000-0000"
                  required
                  autoComplete="tel"
                />
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
                <input
                  id="register-password"
                  type="password"
                  name="password"
                  className="form-control"
                  value={registerData.password}
                  onChange={handleRegisterChange}
                  placeholder="Create a password (min. 8 characters)"
                  required
                  minLength="8"
                  autoComplete="new-password"
                />
                <small className="form-hint">Use at least 8 characters</small>
              </div>

              <div className="form-group">
                <label htmlFor="password_confirmation">Confirm password</label>
                <input
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  className="form-control"
                  value={registerData.password_confirmation}
                  onChange={handleRegisterChange}
                  placeholder="Confirm your password"
                  required
                  autoComplete="new-password"
                />
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
                  'Create account'
                )}
              </button>

              <div className="auth-footer">
                <p className="terms-text text-muted">
                  By signing in or creating an account, you agree with our{' '}
                  <a href="#" className="link-text">Terms & conditions</a> and{' '}
                  <a href="#" className="link-text">Privacy statement</a>.
                </p>
                <p>
                  Already have an account? {' '}
                  <button type="button" onClick={() => { setIsLogin(true); navigate('/login?mode=login'); }} className="link-button">
                    Sign in
                  </button>
                </p>
              </div>
            </form>
          )}

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

export default LoginPage;
