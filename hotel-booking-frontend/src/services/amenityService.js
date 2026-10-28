import { API_BASE_URL } from '../config';

const ADMIN_API_URL = `${API_BASE_URL.replace('/api', '')}/api/admin`;

const getAuthHeaders = () => {
  const token = localStorage.getItem('admin_token');
  return {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  };
};

export const amenityService = {
  // Get all amenities
  async getAmenities() {
    const response = await fetch(`${ADMIN_API_URL}/amenities`, {
      headers: getAuthHeaders()
    });
    return response.json();
  },

  // Get single amenity
  async getAmenity(id) {
    const response = await fetch(`${ADMIN_API_URL}/amenities/${id}`, {
      headers: getAuthHeaders()
    });
    return response.json();
  },

  // Create amenity
  async createAmenity(data) {
    const response = await fetch(`${ADMIN_API_URL}/amenities`, {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Update amenity
  async updateAmenity(id, data) {
    const response = await fetch(`${ADMIN_API_URL}/amenities/${id}`, {
      method: 'PUT',
      headers: getAuthHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Delete amenity
  async deleteAmenity(id) {
    const response = await fetch(`${ADMIN_API_URL}/amenities/${id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
    });
    return response.json();
  }
};
