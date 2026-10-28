import axios from 'axios';
import { API_BASE_URL } from '../config';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Request interceptor to add auth token if available
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Add cache-busting timestamp to prevent browser caching
    // This ensures backend changes are immediately visible in frontend
    if (!config.params) {
      config.params = {};
    }
    config.params._t = Date.now();
    
    // Disable browser caching
    config.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    config.headers['Pragma'] = 'no-cache';
    config.headers['Expires'] = '0';
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response) {
      // Server responded with error
      const data = error.response.data;
      let message = data.message || 'An error occurred';
      
      // If there are validation errors, format them
      if (data.errors && typeof data.errors === 'object') {
        const errorMessages = Object.values(data.errors).flat();
        if (errorMessages.length > 0) {
          message = errorMessages.join(', ');
        }
      }
      
      return Promise.reject(new Error(message));
    } else if (error.request) {
      // Request made but no response
      return Promise.reject(new Error('No response from server'));
    } else {
      // Something else happened
      return Promise.reject(error);
    }
  }
);

// ============================================
// PUBLIC APIs (No Auth Required)
// ============================================

// Settings
export const getSettings = () => {
  return api.get('/public/settings');
};

// Get pricing breakdown (for checkout preview)
export const getPricingBreakdown = (data) => {
  return api.post('/public/bookings/pricing', data);
};

// Page 1: Home / Landing
export const getFeaturedProperties = () => {
  return api.get('/public/properties/featured');
};

// Page 2: Search Results
export const searchProperties = (params) => {
  return api.get('/public/properties/search', { params });
};

// Page 3: Property Detail
export const getPropertyDetails = (propertyId) => {
  return api.get(`/public/properties/${propertyId}`);
};

// Page 4: Room Selection / Availability
export const checkAvailability = (data) => {
  return api.post('/public/availability/check', data);
};

export const getPricingCalendar = (params) => {
  return api.get('/public/availability/calendar', { params });
};

export const searchAvailability = (data) => {
  return api.post('/public/availability/search', data);
};

// Page 5: Checkout - Create Booking Hold
export const createBookingHold = (data) => {
  return api.post('/public/bookings/hold', data);
};

// Page 5: Checkout - Create Payment Intent
export const createPaymentIntent = (data) => {
  return api.post('/public/payment/intent', data);
};

// Page 6: Confirmation - Check Payment Status (Polling)
export const checkPaymentStatus = (paymentIntentId) => {
  return api.get(`/public/payment/status/${paymentIntentId}`);
};

// Page 6: Get Booking by Reference
export const getBookingByReference = (reference) => {
  return api.get(`/public/bookings/reference/${reference}`);
};

// Page 8: My Bookings - Search by Email
export const searchBookingsByEmail = (email) => {
  return api.post('/public/bookings/search', { email });
};

// Page 10: Invoice
export const getInvoiceData = (invoiceNumber) => {
  return api.get(`/public/invoices/${invoiceNumber}`);
};

export const downloadInvoicePDF = (invoiceNumber) => {
  // Use a raw axios instance WITHOUT the response interceptor
  // The interceptor does `response.data` which destroys binary blob responses
  const { API_BASE_URL: baseURL } = require('../config');
  return import('axios').then(({ default: axios }) =>
    axios.get(`${baseURL}/public/invoices/${invoiceNumber}/download`, {
      responseType: 'blob',
      headers: { 'Accept': 'application/pdf' }
    }).then(res => res.data)
  );
};

// ============================================
// GUEST AUTHENTICATION APIs
// ============================================

export const guestRegister = (data) => {
  return api.post('/public/guest/register', data);
};

export const guestLogin = (data) => {
  return api.post('/public/guest/login', data);
};

export const guestLogout = () => {
  return api.post('/public/guest/logout');
};

export const getGuestProfile = () => {
  return api.get('/public/guest/profile');
};

// Page 8: My Bookings (Authenticated)
export const getMyBookings = () => {
  return api.get('/public/guest/bookings');
};

// Page 9: Booking Detail
export const getBookingDetails = (bookingId) => {
  return api.get(`/public/guest/bookings/${bookingId}`);
};

export default api;
