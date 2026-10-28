import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import AdminLayout from '../../components/AdminLayout';
import { amenityService } from '../../services/amenityService';
import '../../AdminStyles.css';

const AmenitiesPage = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [amenities, setAmenities] = useState([]);
  const [filteredAmenities, setFilteredAmenities] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [modalMode, setModalMode] = useState('create'); // 'create' or 'edit'
  const [selectedAmenity, setSelectedAmenity] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    icon: ''
  });
  const [formErrors, setFormErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [deleteConfirm, setDeleteConfirm] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem('admin_token');
    if (!token) {
      navigate('/admin/login');
      return;
    }
    loadAmenities();
  }, [navigate]);

  useEffect(() => {
    // Filter amenities based on search term
    if (searchTerm.trim() === '') {
      setFilteredAmenities(amenities);
    } else {
      const filtered = amenities.filter(amenity =>
        amenity.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (amenity.icon && amenity.icon.toLowerCase().includes(searchTerm.toLowerCase()))
      );
      setFilteredAmenities(filtered);
    }
  }, [searchTerm, amenities]);

  const loadAmenities = async () => {
    try {
      const response = await amenityService.getAmenities();
      if (response.success) {
        setAmenities(response.data);
        setFilteredAmenities(response.data);
      }
    } catch (err) {
      console.error('Error loading amenities:', err);
      showMessage('error', 'Failed to load amenities');
    } finally {
      setLoading(false);
    }
  };

  const showMessage = (type, text) => {
    setMessage({ type, text });
    setTimeout(() => setMessage({ type: '', text: '' }), 4000);
  };

  const handleAddNew = () => {
    setModalMode('create');
    setFormData({ name: '', icon: '' });
    setFormErrors({});
    setSelectedAmenity(null);
    setShowModal(true);
  };

  const handleEdit = (amenity) => {
    setModalMode('edit');
    setFormData({
      name: amenity.name,
      icon: amenity.icon || ''
    });
    setFormErrors({});
    setSelectedAmenity(amenity);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setFormData({ name: '', icon: '' });
    setFormErrors({});
    setSelectedAmenity(null);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear error for this field
    if (formErrors[name]) {
      setFormErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const validateForm = () => {
    const errors = {};
    
    if (!formData.name.trim()) {
      errors.name = 'Name is required';
    } else if (formData.name.length > 100) {
      errors.name = 'Name must be less than 100 characters';
    }
    
    if (formData.icon && formData.icon.length > 50) {
      errors.icon = 'Icon must be less than 50 characters';
    }

    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setSaving(true);
    try {
      let response;
      if (modalMode === 'create') {
        response = await amenityService.createAmenity(formData);
      } else {
        response = await amenityService.updateAmenity(selectedAmenity.id, formData);
      }

      if (response.success) {
        showMessage('success', response.message || `Amenity ${modalMode === 'create' ? 'created' : 'updated'} successfully`);
        handleCloseModal();
        loadAmenities();
      } else if (response.errors) {
        setFormErrors(response.errors);
      } else {
        showMessage('error', response.message || 'Operation failed');
      }
    } catch (err) {
      console.error('Error saving amenity:', err);
      showMessage('error', 'Failed to save amenity');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (amenity) => {
    setDeleteConfirm(amenity);
  };

  const confirmDelete = async () => {
    try {
      const response = await amenityService.deleteAmenity(deleteConfirm.id);
      
      if (response.success) {
        showMessage('success', 'Amenity deleted successfully');
        loadAmenities();
      } else {
        showMessage('error', response.message || 'Failed to delete amenity');
      }
    } catch (err) {
      console.error('Error deleting amenity:', err);
      showMessage('error', 'Failed to delete amenity');
    } finally {
      setDeleteConfirm(null);
    }
  };

  if (loading) {
    return (
      <AdminLayout>
        <div className="admin-loading">
          <div className="admin-spinner-large"></div>
          <p>Loading amenities...</p>
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <div className="admin-page">
        {/* Page Header */}
        <div className="admin-page-header">
          <div>
            <h1>Amenities</h1>
            <p>Manage hotel amenities (WiFi, Pool, Parking, etc.)</p>
          </div>
          <button className="admin-btn admin-btn-primary" onClick={handleAddNew}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Add Amenity
          </button>
        </div>

        {/* Message Alert */}
        {message.text && (
          <div className={`admin-alert admin-alert-${message.type}`}>
            <span>{message.text}</span>
            <button onClick={() => setMessage({ type: '', text: '' })}>×</button>
          </div>
        )}

        {/* Search Bar */}
        <div className="admin-search-bar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
          <input
            type="text"
            placeholder="Search amenities..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        {/* Data Table */}
        <div className="admin-card">
          <div className="admin-table-container">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Icon</th>
                  <th>Room Types</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredAmenities.length === 0 ? (
                  <tr>
                    <td colSpan="6" style={{ textAlign: 'center', padding: '40px' }}>
                      <p>No amenities found</p>
                    </td>
                  </tr>
                ) : (
                  filteredAmenities.map((amenity) => (
                    <tr key={amenity.id}>
                      <td>{amenity.id}</td>
                      <td>
                        <strong>{amenity.name}</strong>
                      </td>
                      <td>
                        {amenity.icon ? (
                          <span className="admin-badge admin-badge-info">{amenity.icon}</span>
                        ) : (
                          <span className="admin-badge admin-badge-secondary">None</span>
                        )}
                      </td>
                      <td>
                        <span className="admin-badge admin-badge-primary">
                          {amenity.room_types_count || 0} room types
                        </span>
                      </td>
                      <td>{new Date(amenity.created_at).toLocaleDateString()}</td>
                      <td>
                        <div className="admin-table-actions">
                          <button
                            className="admin-btn-icon admin-btn-icon-primary"
                            onClick={() => handleEdit(amenity)}
                            title="Edit"
                          >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>
                          </button>
                          <button
                            className="admin-btn-icon admin-btn-icon-danger"
                            onClick={() => handleDelete(amenity)}
                            title="Delete"
                          >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                              <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Side Modal for Create/Edit */}
        {showModal && (
          <div className="admin-modal-overlay" onClick={handleCloseModal}>
            <div className="admin-modal-panel" onClick={(e) => e.stopPropagation()}>
              <div className="admin-modal-header">
                <h2>{modalMode === 'create' ? 'Add New Amenity' : 'Edit Amenity'}</h2>
                <button className="admin-modal-close" onClick={handleCloseModal}>×</button>
              </div>

              <form onSubmit={handleSubmit} className="admin-modal-body">
                {/* Name Field */}
                <div className="admin-form-group">
                  <label className="admin-label">
                    Amenity Name <span className="required">*</span>
                  </label>
                  <input
                    type="text"
                    name="name"
                    className={`admin-input ${formErrors.name ? 'error' : ''}`}
                    placeholder="e.g., Free WiFi, Swimming Pool"
                    value={formData.name}
                    onChange={handleInputChange}
                    maxLength="100"
                  />
                  {formErrors.name && (
                    <span className="admin-error-message">{formErrors.name}</span>
                  )}
                </div>

                {/* Icon Field */}
                <div className="admin-form-group">
                  <label className="admin-label">Icon Name (Optional)</label>
                  <input
                    type="text"
                    name="icon"
                    className={`admin-input ${formErrors.icon ? 'error' : ''}`}
                    placeholder="e.g., wifi, pool, parking"
                    value={formData.icon}
                    onChange={handleInputChange}
                    maxLength="50"
                  />
                  {formErrors.icon && (
                    <span className="admin-error-message">{formErrors.icon}</span>
                  )}
                  <small className="admin-help-text">
                    Common icons: wifi, pool, parking, restaurant, gym, spa, bar, breakfast, ac, tv, safe, minibar
                  </small>
                </div>

                {/* Form Actions */}
                <div className="admin-modal-footer">
                  <button
                    type="button"
                    className="admin-btn admin-btn-secondary"
                    onClick={handleCloseModal}
                    disabled={saving}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="admin-btn admin-btn-primary"
                    disabled={saving}
                  >
                    {saving ? (
                      <>
                        <span className="admin-spinner"></span>
                        Saving...
                      </>
                    ) : (
                      modalMode === 'create' ? 'Create Amenity' : 'Update Amenity'
                    )}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Delete Confirmation Dialog */}
        {deleteConfirm && (
          <div className="admin-modal-overlay" onClick={() => setDeleteConfirm(null)}>
            <div className="admin-modal-dialog" onClick={(e) => e.stopPropagation()}>
              <div className="admin-modal-header">
                <h3>Confirm Delete</h3>
                <button className="admin-modal-close" onClick={() => setDeleteConfirm(null)}>×</button>
              </div>
              <div className="admin-modal-body">
                <p>Are you sure you want to delete <strong>{deleteConfirm.name}</strong>?</p>
                {deleteConfirm.room_types_count > 0 && (
                  <div className="admin-alert admin-alert-warning" style={{ marginTop: '15px' }}>
                    Warning: This amenity is used by {deleteConfirm.room_types_count} room type(s).
                  </div>
                )}
              </div>
              <div className="admin-modal-footer">
                <button
                  className="admin-btn admin-btn-secondary"
                  onClick={() => setDeleteConfirm(null)}
                >
                  Cancel
                </button>
                <button
                  className="admin-btn admin-btn-danger"
                  onClick={confirmDelete}
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
};

export default AmenitiesPage;
