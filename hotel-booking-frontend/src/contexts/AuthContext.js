import React, { createContext, useContext, useState, useEffect } from 'react';
import { guestLogin, guestRegister, guestLogout, getGuestProfile } from '../services/api';

const AuthContext = createContext(null);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Check if user is logged in on mount
  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    const token = localStorage.getItem('auth_token');
    const cachedUser = localStorage.getItem('user_data');
    
    if (!token) {
      // No token means user is not logged in
      // Clear any leftover booking data from previous sessions
      clearBookingData();
      setLoading(false);
      return;
    }

    // Optimistically set user from cache to prevent flash
    if (cachedUser) {
      try {
        const parsedUser = JSON.parse(cachedUser);
        setUser(parsedUser);
        
        // Validate booking data belongs to this user
        validateBookingDataOwnership(parsedUser);
        
        setLoading(false); // Set loading to false immediately when we have cached data
      } catch (e) {
        console.error('Failed to parse cached user data:', e);
      }
    }

    // Verify token in background (won't show loading spinner)
    try {
      const response = await getGuestProfile();
      if (response.success) {
        setUser(response.data);
        
        // Validate booking data belongs to this user
        validateBookingDataOwnership(response.data);
        
        // Update cache with fresh data
        localStorage.setItem('user_data', JSON.stringify(response.data));
      } else {
        // Token is invalid, clear everything
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        clearBookingData();
        setUser(null);
      }
    } catch (err) {
      console.error('Auth check failed:', err);
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      clearBookingData();
      setUser(null);
    } finally {
      // Ensure loading is false (in case there was no cached data)
      setLoading(false);
    }
  };

  const validateBookingDataOwnership = (currentUser) => {
    if (!currentUser) return;
    
    // Check if booking_data exists
    const bookingDataStr = localStorage.getItem('booking_data');
    if (!bookingDataStr) return;
    
    try {
      const bookingData = JSON.parse(bookingDataStr);
      
      // If booking data has a guest_id or user_id, verify it matches current user
      if (bookingData.guest_id && bookingData.guest_id !== currentUser.id) {
        console.warn('Booking data belongs to different user, clearing...');
        clearBookingData();
      } else if (bookingData.user_id && bookingData.user_id !== currentUser.id) {
        console.warn('Booking data belongs to different user, clearing...');
        clearBookingData();
      }
    } catch (e) {
      console.error('Failed to validate booking data:', e);
      // If we can't parse it, clear it for safety
      clearBookingData();
    }
  };

  const clearBookingData = () => {
    // Clear booking-related data
    localStorage.removeItem('booking_data');
    localStorage.removeItem('bookingData');
    localStorage.removeItem('propertyData');
    localStorage.removeItem('roomData');
    localStorage.removeItem('selectedRooms');
    localStorage.removeItem('checkInDate');
    localStorage.removeItem('checkOutDate');
    localStorage.removeItem('guests');
    localStorage.removeItem('booking_id');
    localStorage.removeItem('payment_intent');
    localStorage.removeItem('booking_intent');
    localStorage.removeItem('redirect_after_login');
    
    // Remove all keys that start with booking_, room_, payment_, guest_, redirect_
    Object.keys(localStorage).forEach(key => {
      if (key.startsWith('booking_') || 
          key.startsWith('room_') || 
          key.startsWith('payment_') ||
          key.startsWith('guest_') ||
          key.startsWith('redirect_')) {
        localStorage.removeItem(key);
      }
    });
  };

  const login = async (email, password) => {
    try {
      setError(null);
      const response = await guestLogin({ email, password });
      
      if (response.success) {
        // Clear any previous user's booking data FIRST
        clearBookingData();
        
        // Then store token and user data
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.guest));
        setUser(response.data.guest);
        return { success: true, user: response.data.guest };
      } else {
        setError(response.message || 'Login failed');
        return { success: false, message: response.message };
      }
    } catch (err) {
      const message = err.message || 'Login failed';
      setError(message);
      return { success: false, message };
    }
  };

  const register = async (userData) => {
    try {
      setError(null);
      const response = await guestRegister(userData);
      
      if (response.success) {
        // Clear any previous user's booking data FIRST
        clearBookingData();
        
        // Then store token and user data
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.guest));
        setUser(response.data.guest);
        return { success: true, user: response.data.guest };
      } else {
        setError(response.message || 'Registration failed');
        return { success: false, message: response.message };
      }
    } catch (err) {
      const message = err.message || 'Registration failed';
      setError(message);
      return { success: false, message };
    }
  };

  const logout = async () => {
    try {
      await guestLogout();
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      // Clear ALL user-specific data from localStorage
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      
      // Clear all booking data
      clearBookingData();
      
      setUser(null);
    }
  };

  const value = {
    user,
    loading,
    error,
    isAuthenticated: !!user,
    login,
    register,
    logout,
    checkAuth,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export default AuthContext;
