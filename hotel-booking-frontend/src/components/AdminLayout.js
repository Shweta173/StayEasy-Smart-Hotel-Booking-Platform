import { useState, useEffect } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import '../AdminStyles.css';

const AdminLayout = ({ children }) => {
  const navigate = useNavigate();
  const location = useLocation();
  const [adminUser, setAdminUser] = useState(null);
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [showLogoutModal, setShowLogoutModal] = useState(false);

  useEffect(() => {
    // Check authentication
    const token = localStorage.getItem('admin_token');
    const user = localStorage.getItem('admin_user');

    if (!token || !user) {
      navigate('/admin/login');
      return;
    }

    setAdminUser(JSON.parse(user));
  }, [navigate]);

  const handleLogout = () => {
    setShowLogoutModal(true);
  };

  const confirmLogout = () => {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_user');
    navigate('/admin/login');
  };

  const cancelLogout = () => {
    setShowLogoutModal(false);
  };

  const isActive = (path) => {
    return location.pathname === path || location.pathname.startsWith(path + '/');
  };

  if (!adminUser) {
    return (
      <div className="admin-loading">
        <div className="admin-spinner-large"></div>
      </div>
    );
  }

  // Check permissions
  const canManageProperties = ['admin', 'manager'].includes(adminUser.role);

  return (
    <div className="admin-layout">
      {/* Sidebar */}
      <aside className={`admin-sidebar ${sidebarOpen ? '' : 'collapsed'}`}>
        <div className="admin-sidebar-header">
          <div className="admin-sidebar-logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
              <rect x="3" y="3" width="18" height="18" rx="2" stroke="#fff" strokeWidth="2"/>
              <path d="M8 8h8M8 12h8M8 16h4" stroke="#fff" strokeWidth="2" strokeLinecap="round"/>
            </svg>
            {sidebarOpen && <span>Admin Portal</span>}
          </div>
        </div>

        <nav className="admin-sidebar-nav">
          <Link 
            to="/admin/dashboard" 
            className={`admin-nav-item ${isActive('/admin/dashboard') ? 'active' : ''}`}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            {sidebarOpen && <span>Dashboard</span>}
          </Link>

          <Link 
            to="/admin/bookings" 
            className={`admin-nav-item ${isActive('/admin/bookings') ? 'active' : ''}`}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
            </svg>
            {sidebarOpen && <span>Bookings</span>}
          </Link>

          {canManageProperties && (
            <>
              <Link 
                to="/admin/properties" 
                className={`admin-nav-item ${isActive('/admin/properties') ? 'active' : ''}`}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
                </svg>
                {sidebarOpen && <span>Properties</span>}
              </Link>

              <Link 
                to="/admin/room-types" 
                className={`admin-nav-item ${isActive('/admin/room-types') ? 'active' : ''}`}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M21 10.78V8c0-1.65-1.35-3-3-3h-4c-.77 0-1.47.3-2 .78-.53-.48-1.23-.78-2-.78H6C4.35 5 3 6.35 3 8v2.78c-.61.55-1 1.34-1 2.22v6h2v-2h16v2h2v-6c0-.88-.39-1.67-1-2.22z"/>
                </svg>
                {sidebarOpen && <span>Room Types</span>}
              </Link>

              <Link 
                to="/admin/amenities" 
                className={`admin-nav-item ${isActive('/admin/amenities') ? 'active' : ''}`}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                {sidebarOpen && <span>✨ Amenities ✨</span>}
              </Link>

              <Link 
                to="/admin/rate-plans" 
                className={`admin-nav-item ${isActive('/admin/rate-plans') ? 'active' : ''}`}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                </svg>
                {sidebarOpen && <span>Rate Plans</span>}
              </Link>
            </>
          )}

          <Link 
            to="/admin/orders" 
            className={`admin-nav-item ${isActive('/admin/orders') ? 'active' : ''}`}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>
            {sidebarOpen && <span>Orders</span>}
          </Link>

          <Link 
            to="/admin/invoices" 
            className={`admin-nav-item ${isActive('/admin/invoices') ? 'active' : ''}`}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
            </svg>
            {sidebarOpen && <span>Invoices</span>}
          </Link>
        </nav>

        <div className="admin-sidebar-footer">
          <button 
            className="admin-sidebar-toggle"
            onClick={() => setSidebarOpen(!sidebarOpen)}
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <div className="admin-main">
        {/* Header */}
        <header className="admin-header">
          <div className="admin-header-left">
            <h2>Hotel Booking CMS</h2>
          </div>
          <div className="admin-header-right">
            <div className="admin-user-menu">
              <div className="admin-user-info">
                <span className="admin-user-name">{adminUser.name}</span>
                <span className="admin-user-role">{adminUser.role}</span>
              </div>
              <div className="admin-user-avatar">
                {adminUser.name.charAt(0).toUpperCase()}
              </div>
              <div className="admin-user-dropdown">
                <button onClick={handleLogout} className="admin-dropdown-item">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                  </svg>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="admin-content">
          {children}
        </main>
      </div>

      {/* Logout Confirmation Modal */}
      {showLogoutModal && (
        <div className="modal-overlay" onClick={cancelLogout}>
          <div className="modal-content logout-modal" onClick={(e) => e.stopPropagation()}>
            <h3 className="modal-title">Log Out?</h3>
            <p className="modal-message">Are you sure want to log out from admin panel?</p>
            <div className="modal-actions">
              <button className="btn-modal-secondary" onClick={cancelLogout}>
                Cancel
              </button>
              <button className="btn-modal-primary" onClick={confirmLogout}>
                Log out
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdminLayout;
