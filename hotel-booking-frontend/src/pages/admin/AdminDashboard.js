import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import AdminLayout from '../../components/AdminLayout';
import { API_BASE_URL } from '../../config';
import '../../AdminStyles.css';

const ADMIN_API_URL = `${API_BASE_URL.replace('/api', '')}/api/admin`;

const AdminDashboard = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem('admin_token');
    if (!token) {
      navigate('/admin/login');
      return;
    }

    loadDashboardData();
  }, [navigate]);

  const loadDashboardData = async () => {
    try {
      const token = localStorage.getItem('admin_token');
      const response = await fetch(`${ADMIN_API_URL}/dashboard`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        setStats(data.statistics);
      } else {
        setError('Failed to load dashboard data');
      }
    } catch (err) {
      console.error('Dashboard error:', err);
      setError('Unable to load dashboard. Please check your connection.');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <AdminLayout>
        <div className="admin-loading">
          <div className="admin-spinner-large"></div>
          <p>Loading dashboard...</p>
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <div className="admin-dashboard">
        <div className="admin-page-header">
          <h1>Dashboard</h1>
          <p>Welcome back! Here's what's happening today.</p>
        </div>

        {error && (
          <div className="admin-alert admin-alert-error">
            <span>{error}</span>
          </div>
        )}

        {/* Stats Grid */}
        <div className="admin-stats-grid">
          <div className="admin-stat-card">
            <div className="stat-icon stat-icon-blue">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
              </svg>
            </div>
            <div className="stat-content">
              <h3>Total Properties</h3>
              <p className="stat-value">{stats?.total_properties || 0}</p>
              <span className="stat-label">Active properties</span>
            </div>
          </div>

          <div className="admin-stat-card">
            <div className="stat-icon stat-icon-green">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
            <div className="stat-content">
              <h3>Total Bookings</h3>
              <p className="stat-value">{stats?.total_bookings || 0}</p>
              <span className="stat-label">All time</span>
            </div>
          </div>

          <div className="admin-stat-card">
            <div className="stat-icon stat-icon-yellow">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
              </svg>
            </div>
            <div className="stat-content">
              <h3>Today's Check-ins</h3>
              <p className="stat-value">{stats?.todays_checkins || 0}</p>
              <span className="stat-label">Expected arrivals</span>
            </div>
          </div>

          <div className="admin-stat-card">
            <div className="stat-icon stat-icon-purple">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
              </svg>
            </div>
            <div className="stat-content">
              <h3>Total Revenue</h3>
              <p className="stat-value">${stats?.total_revenue?.toLocaleString() || '0'}</p>
              <span className="stat-label">All time</span>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="admin-quick-actions">
          <h2>Quick Actions</h2>
          <div className="admin-actions-grid">
            <button 
              className="admin-action-card"
              onClick={() => navigate('/admin/bookings')}
            >
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
              </svg>
              <h3>View All Bookings</h3>
              <p>Manage reservations and check-ins</p>
            </button>

            <button 
              className="admin-action-card"
              onClick={() => navigate('/admin/bookings/today')}
            >
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
              </svg>
              <h3>Today's Activity</h3>
              <p>Arrivals and departures</p>
            </button>

            <button 
              className="admin-action-card"
              onClick={() => navigate('/admin/properties')}
            >
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
              </svg>
              <h3>Manage Properties</h3>
              <p>Add or edit hotel properties</p>
            </button>

            <button 
              className="admin-action-card"
              onClick={() => navigate('/admin/orders')}
            >
              <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V7h10v2z"/>
              </svg>
              <h3>View Orders</h3>
              <p>Track payments and invoices</p>
            </button>
          </div>
        </div>

        {/* Recent Activity */}
        <div className="admin-recent-activity">
          <h2>Recent Bookings</h2>
          <div className="admin-card">
            {stats?.recent_bookings && stats.recent_bookings.length > 0 ? (
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Booking Ref</th>
                    <th>Guest</th>
                    <th>Property</th>
                    <th>Check-in</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.recent_bookings.map((booking) => (
                    <tr key={booking.id}>
                      <td><strong>{booking.booking_reference}</strong></td>
                      <td>{booking.guest_name}</td>
                      <td>{booking.property_name}</td>
                      <td>{booking.check_in_date}</td>
                      <td>
                        <span className={`admin-badge admin-badge-${booking.status}`}>
                          {booking.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : (
              <div className="admin-empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
                  <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
                </svg>
                <h3>No Recent Bookings</h3>
                <p>New bookings will appear here</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </AdminLayout>
  );
};

export default AdminDashboard;
