import React from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useLocation, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ToastProvider, useToast } from './contexts/ToastContext';
import HomePage from './pages/HomePage';
import SearchPage from './pages/SearchPage';
import PropertyDetailPage from './pages/PropertyDetailPage';
import RoomSelectionPage from './pages/RoomSelectionPage';
import CheckoutPage from './pages/CheckoutPage';
import ConfirmingPage from './pages/ConfirmingPage';
import ConfirmationPage from './pages/ConfirmationPage';
import LoginPage from './pages/LoginPage';
import GuestLoginPage from './pages/GuestLoginPage';
import GuestRegisterPage from './pages/GuestRegisterPage';
import MyBookingsPage from './pages/MyBookingsPage';
import BookingDetailPage from './pages/BookingDetailPage';
import InvoicePage from './pages/InvoicePage';
import ReviewsPage from './pages/ReviewsPage';
import NotFoundPage from './pages/NotFoundPage';
// Admin Pages
import AdminLoginPage from './pages/admin/AdminLoginPage';
import AdminDashboard from './pages/admin/AdminDashboard';
import AmenitiesPage from './pages/admin/AmenitiesPage';
import './App.css';

/**
 * Modern Professional Navbar
 * Inspired by Booking.com and Airbnb
 * WITH AUTHENTICATION
 */
function AppHeader() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);
  const [showLogoutModal, setShowLogoutModal] = React.useState(false);
  const location = useLocation();
  const { user, isAuthenticated, logout, loading } = useAuth();
  const toast = useToast();
  
  // Hide header on login and registration pages
  const hideHeader = location.pathname === '/login' || location.pathname === '/register';
  
  if (hideHeader) {
    return null; // Don't render header on login/register pages
  }

  const handleLogout = () => {
    setShowLogoutModal(true);
  };

  const confirmLogout = () => {
    logout();
    setShowLogoutModal(false);
    setIsMobileMenuOpen(false);
    
    // Show success toast
    toast.success('Logged Out Successfully', 'You have been logged out from your account.');
  };

  const cancelLogout = () => {
    setShowLogoutModal(false);
  };

  return (
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
          {/* Show My Bookings only when authenticated */}
          {isAuthenticated && (
            <Link to="/my-bookings" className="nav-link">My Bookings</Link>
          )}
        </nav>

        {/* Desktop Auth Buttons */}
        <div className="navbar-auth">
          {loading ? (
            // Show loading state while checking authentication
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <div className="spinner-small" style={{ 
                width: '20px', 
                height: '20px', 
                border: '2px solid #f3f3f3',
                borderTop: '2px solid #003580',
                borderRadius: '50%',
                animation: 'spin 0.8s linear infinite'
              }}></div>
            </div>
          ) : isAuthenticated ? (
            <>
              <span className="user-greeting">Hello, {user?.first_name}!</span>
              <button onClick={handleLogout} className="btn-signin">Logout</button>
            </>
          ) : (
            <>
              <Link to="/login" className="btn-signin">Sign In</Link>
              <Link to="/register" className="btn-register">Register</Link>
            </>
          )}
        </div>

        {/* Mobile Hamburger */}
        <button 
          className="mobile-menu-toggle"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          aria-label="Toggle menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="mobile-menu">
          <Link to="/" className="mobile-link" onClick={() => setIsMobileMenuOpen(false)}>Home</Link>
          <Link to="/search" className="mobile-link" onClick={() => setIsMobileMenuOpen(false)}>Properties</Link>
          {/* Show My Bookings only when authenticated */}
          {isAuthenticated && (
            <Link to="/my-bookings" className="mobile-link" onClick={() => setIsMobileMenuOpen(false)}>My Bookings</Link>
          )}
          <div className="mobile-auth">
            {loading ? (
              // Show loading state while checking authentication
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '10px' }}>
                <div className="spinner-small" style={{ 
                  width: '20px', 
                  height: '20px', 
                  border: '2px solid #f3f3f3',
                  borderTop: '2px solid #003580',
                  borderRadius: '50%',
                  animation: 'spin 0.8s linear infinite'
                }}></div>
              </div>
            ) : isAuthenticated ? (
              <>
                <span className="user-greeting-mobile">Hello, {user?.first_name}!</span>
                <button onClick={handleLogout} className="btn-signin">Logout</button>
              </>
            ) : (
              <>
                <Link to="/login" className="btn-signin" onClick={() => setIsMobileMenuOpen(false)}>Sign In</Link>
                <Link to="/register" className="btn-register" onClick={() => setIsMobileMenuOpen(false)}>Register</Link>
              </>
            )}
          </div>
        </div>
      )}

      {/* Logout Confirmation Modal */}
      {showLogoutModal && (
        <div className="modal-overlay" onClick={cancelLogout}>
          <div className="modal-content logout-modal" onClick={(e) => e.stopPropagation()}>
            <h3 className="modal-title" style={{ textAlign: 'center', borderBottom: 'none', paddingBottom: '8px' }}>Logout</h3>
            <p className="modal-message" style={{ textAlign: 'center', borderBottom: 'none', paddingBottom: '0', marginBottom: '12px' }}>Are you sure you want to logout?</p>
            <div className="modal-actions" style={{ borderTop: 'none', paddingTop: '12px' }}>
              <button className="btn-modal-secondary" onClick={cancelLogout}>
                Cancel
              </button>
              <button className="btn-modal-primary" onClick={confirmLogout} style={{ background: '#dc2626' }}>
                Yes, Logout
              </button>
            </div>
          </div>
        </div>
      )}
    </header>
  );
}

/**
 * Protected Route Component
 * Redirects to login if user is not authenticated
 * Preserves the current path to redirect back after login
 */
function ProtectedRoute({ children }) {
  const { isAuthenticated, loading } = useAuth();
  const location = useLocation();
  
  if (loading) {
    return (
      <div style={{ 
        padding: '50px', 
        textAlign: 'center',
        minHeight: '400px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexDirection: 'column'
      }}>
        <div className="spinner-large"></div>
        <p style={{ marginTop: '20px', color: '#6B7280' }}>Loading...</p>
      </div>
    );
  }
  
  if (!isAuthenticated) {
    // Redirect to login with the current path as redirect parameter
    return <Navigate to={`/login?redirect=${encodeURIComponent(location.pathname)}`} replace />;
  }
  
  return children;
}

/**
 * Main App Component
 * 
 * Hotel Booking Platform - Frontend
 * Implements the complete 10-page booking flow following PDF requirements
 */
function App() {
  return (
    <Router>
      <AuthProvider>
        <ToastProvider>
          <AppContent />
        </ToastProvider>
      </AuthProvider>
    </Router>
  );
}

function AppContent() {
  const location = useLocation();
  
  // Define valid routes
  const validRoutes = [
    '/',
    '/search',
    '/property',
    '/rooms',
    '/checkout',
    '/confirming',
    '/confirmation',
    '/my-bookings',
    '/booking',
    '/invoice',
    '/reviews',
    '/admin'
  ];
  
  // Check if current path matches any valid route (including dynamic segments)
  const isValidRoute = validRoutes.some(route => 
    location.pathname === route || location.pathname.startsWith(route + '/')
  );
  
  // Hide header and footer on login, registration pages, and 404 (invalid routes)
  const hideHeaderFooter = location.pathname === '/login' || 
                          location.pathname === '/register' ||
                          location.pathname === '/login-old' ||
                          !isValidRoute;

  return (
    <div className="App">
      {/* Toast Notification Container */}
      <ToastContainer 
        position="top-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="light"
      />
      
      {/* Header with Dynamic Navigation - Hidden on login/register */}
      {!hideHeaderFooter && <AppHeader />}

      {/* Main Content */}
      <main className="app-main">
        <Routes>
          {/* Page 1: Home/Landing */}
          <Route path="/" element={<HomePage />} />

          {/* Page 2: Search Results */}
          <Route path="/search" element={<SearchPage />} />

          {/* Page 3: Property Detail */}
          <Route path="/property/:id" element={<PropertyDetailPage />} />

          {/* Page 4: Room Selection */}
          <Route path="/rooms/:propertyId" element={<RoomSelectionPage />} />

          {/* Page 5: Checkout (Guest Details + Payment) - PROTECTED */}
          <Route path="/checkout" element={
            <ProtectedRoute>
              <CheckoutPage />
            </ProtectedRoute>
          } />

          {/* Page 6a: Payment Confirmation (Polling) */}
          <Route path="/confirming" element={<ConfirmingPage />} />

          {/* Page 6b: Booking Confirmation */}
          <Route path="/confirmation/:reference" element={<ConfirmationPage />} />
          
          {/* Helper: Latest Booking Redirect (for testing) */}

          {/* Page 7: Guest Login/Register - NEW SEPARATE PAGES */}
          <Route path="/login" element={<GuestLoginPage />} />
          <Route path="/register" element={<GuestRegisterPage />} />
          
          {/* OLD Combined Login/Register Page (keeping for backward compatibility) */}
          <Route path="/login-old" element={<LoginPage />} />

          {/* Page 8: My Bookings (Protected - Requires Authentication) */}
          <Route path="/my-bookings" element={
            <ProtectedRoute>
              <MyBookingsPage />
            </ProtectedRoute>
          } />

          {/* Page 9: Booking Detail */}
          <Route path="/booking/:id" element={<BookingDetailPage />} />

          {/* Page 10: Invoice */}
          <Route path="/invoice/:invoiceNumber" element={<InvoicePage />} />

          {/* Page 11: Reviews */}
          <Route path="/reviews" element={<ReviewsPage />} />

          {/* ============================================
              ADMIN PORTAL ROUTES
              ============================================ */}
          
          {/* Admin Login */}
          <Route path="/admin/login" element={<AdminLoginPage />} />
          
          {/* Admin Dashboard */}
          <Route path="/admin/dashboard" element={<AdminDashboard />} />
          
          {/* Admin Amenities */}
          <Route path="/admin/amenities" element={<AmenitiesPage />} />
          
          {/* More admin routes will be added here */}

          {/* 404 Not Found - Catch all invalid routes */}
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>

      {/* Footer - Hidden on login/register */}
      {!hideHeaderFooter && (
        <footer className="app-footer">
          <div className="container">
            <p>&copy; 2026 Hotel Booking Platform. All rights reserved.</p>
            <p className="footer-note">
             
            </p>
          </div>
        </footer>
      )}
    </div>
  );
}

export default App;
