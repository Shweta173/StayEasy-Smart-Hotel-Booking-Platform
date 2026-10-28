import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { API_BASE_URL } from '../../config';
import '../../AdminStyles.css';

const ADMIN_API_URL = `${API_BASE_URL.replace('/api', '')}/api/admin`;

const AdminLoginPage = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await fetch(`${ADMIN_API_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (data.success) {
        // Save token and admin info to localStorage
        localStorage.setItem('admin_token', data.token);
        localStorage.setItem('admin_user', JSON.stringify(data.admin));
        
        // Show success toast
        toast.success('Login successful! Welcome back, ' + data.admin.name, {
          position: "top-right",
          autoClose: 3000,
        });
        
        // Redirect to admin dashboard after a brief delay
        setTimeout(() => {
          navigate('/admin/dashboard');
        }, 500);
      } else {
        const errorMsg = data.message || 'Login failed. Please check your credentials.';
        setError(errorMsg);
        toast.error(errorMsg);
      }
    } catch (err) {
      console.error('Login error:', err);
      const errorMsg = 'Unable to connect to server. Please make sure the backend is running.';
      setError(errorMsg);
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const quickLogin = (email, password) => {
    setFormData({ email, password });
  };

  return (
    <div className="admin-login-wrapper">
      <div className="admin-login-container">
        <div className="admin-login-card">
          {/* Logo and Title */}
          <div className="admin-login-header">
            <div className="admin-logo">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="3" width="18" height="18" rx="2" stroke="#0071c2" strokeWidth="2"/>
                <path d="M8 8h8M8 12h8M8 16h4" stroke="#0071c2" strokeWidth="2" strokeLinecap="round"/>
              </svg>
            </div>
            <h1>Admin Portal</h1>
            <p>Hotel Booking Platform</p>
          </div>

          {/* Login Form */}
          <form onSubmit={handleSubmit} className="admin-login-form">
            {error && (
              <div className="admin-alert admin-alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <span>{error}</span>
              </div>
            )}

            <div className="admin-form-group">
              <label htmlFor="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="admin@hotel.com"
                required
                autoComplete="username"
              />
            </div>

            <div className="admin-form-group">
              <label htmlFor="password">Password</label>
              <input
                type="password"
                id="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="Enter your password"
                required
                autoComplete="current-password"
              />
            </div>

            <button 
              type="submit" 
              className="admin-btn-primary admin-btn-large"
              disabled={loading}
            >
              {loading ? (
                <>
                  <span className="admin-spinner-small"></span>
                  Signing in...
                </>
              ) : (
                'Sign In'
              )}
            </button>
          </form>

          {/* Quick Login for Testing */}
          <div className="admin-quick-login">
            <p className="admin-quick-login-title">Quick Login (Test Accounts)</p>
            <div className="admin-quick-login-buttons">
              <button 
                type="button"
                onClick={() => quickLogin('admin@hotel.com', 'admin123')}
                className="admin-quick-login-btn"
              >
                <div className="role-badge role-admin">Admin</div>
                <div className="role-email">admin@hotel.com</div>
              </button>
              <button 
                type="button"
                onClick={() => quickLogin('manager@hotel.com', 'manager123')}
                className="admin-quick-login-btn"
              >
                <div className="role-badge role-manager">Manager</div>
                <div className="role-email">manager@hotel.com</div>
              </button>
              <button 
                type="button"
                onClick={() => quickLogin('frontdesk@hotel.com', 'frontdesk123')}
                className="admin-quick-login-btn"
              >
                <div className="role-badge role-frontdesk">Front Desk</div>
                <div className="role-email">frontdesk@hotel.com</div>
              </button>
            </div>
          </div>

          {/* Footer */}
          <div className="admin-login-footer">
            <p>© 2026 Hotel Booking Platform. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminLoginPage;
