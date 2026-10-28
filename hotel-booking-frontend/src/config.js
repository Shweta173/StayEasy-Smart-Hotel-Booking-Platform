// API Configuration
// Use environment variable or fallback to localhost for development
export const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://127.0.0.1:8000/api';
export const STORAGE_URL = process.env.REACT_APP_STORAGE_URL || 'http://127.0.0.1:8000/storage';

// Stripe Configuration
export const STRIPE_PUBLISHABLE_KEY = process.env.REACT_APP_STRIPE_KEY || '';

// App Configuration
export const HOLD_EXPIRY_MINUTES = parseInt(process.env.REACT_APP_HOLD_EXPIRY_MINUTES) || 15;
export const PAYMENT_POLLING_INTERVAL = parseInt(process.env.REACT_APP_PAYMENT_POLLING_INTERVAL) || 2000; // 2 seconds
export const PAYMENT_POLLING_MAX_ATTEMPTS = parseInt(process.env.REACT_APP_PAYMENT_POLLING_MAX_ATTEMPTS) || 15; // 30 seconds total
