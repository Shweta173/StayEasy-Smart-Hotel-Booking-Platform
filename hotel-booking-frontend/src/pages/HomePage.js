import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { getFeaturedProperties, searchProperties } from '../services/api';
import { STORAGE_URL } from '../config';
import { useRef } from 'react';

/**
 * Page 1: Home / Landing Page
 * Shows hero section by default
 * Shows search results with filters when searched
 */
const HomePage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const guestDropdownRef = useRef(null);
  const [properties, setProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showResults, setShowResults] = useState(false);
  const [totalResults, setTotalResults] = useState(0);
  const [showGuestDropdown, setShowGuestDropdown] = useState(false);
  const [showFilterModal, setShowFilterModal] = useState(false);
  
  // Collapsible filter sections state
  const [expandedFilters, setExpandedFilters] = useState({
    budget: true,
    popular: true,
    rating: false
  });
  
  // Toggle filter section
  const toggleFilter = (filterName) => {
    setExpandedFilters({
      ...expandedFilters,
      [filterName]: !expandedFilters[filterName]
    });
  };
  
  // Validation state
  const [validationErrors, setValidationErrors] = useState({
    location: false,
    checkIn: false,
    checkOut: false
  });
  
  // Search form state
  const [searchData, setSearchData] = useState({
    location: '',
    checkIn: '',
    checkOut: '',
    guests: 2,
    children: 0,
    rooms: 1
  });

  // Filters state
  const [filters, setFilters] = useState({
    minPrice: 0,
    maxPrice: 10000,
    amenities: [],
    propertyTypes: [],
    rating: 0
  });

  // Handle rating filter change
  const handleRatingChange = (value) => {
    setFilters({...filters, rating: value});
    applyFilters({...filters, rating: value});
  };

  // Apply filters and reload search results
  const applyFilters = (updatedFilters = filters) => {
    const params = new URLSearchParams(location.search);
    params.set('min_price', updatedFilters.minPrice);
    params.set('max_price', updatedFilters.maxPrice);
    params.set('rating', updatedFilters.rating);
    navigate(`/?${params.toString()}`);
  };

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    if (params.get('location')) {
      loadSearchResults(params);
      setShowResults(true);
    } else {
      loadFeaturedProperties();
      setShowResults(false);
    }
  }, [location.search]);

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (guestDropdownRef.current && !guestDropdownRef.current.contains(event.target)) {
        setShowGuestDropdown(false);
      }
    };

    if (showGuestDropdown) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [showGuestDropdown]);

  const loadFeaturedProperties = async () => {
    try {
      setLoading(true);
      const response = await getFeaturedProperties();
      if (response.success) {
        setProperties(response.properties || []);
      }
    } catch (error) {
      console.error('Failed to fetch properties:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadSearchResults = async (params) => {
    try {
      setLoading(true);
      setSearchData({
        location: params.get('location') || '',
        checkIn: params.get('check_in') || '',
        checkOut: params.get('check_out') || '',
        guests: parseInt(params.get('guests')) || 2,
        children: parseInt(params.get('children')) || 0,
        rooms: parseInt(params.get('rooms')) || 1
      });

      const searchParams = {
        location: params.get('location'),
        check_in: params.get('check_in'),
        check_out: params.get('check_out'),
        guests: params.get('guests'),
        rooms: params.get('rooms'),
        min_price: params.get('min_price') || filters.minPrice,
        max_price: params.get('max_price') || filters.maxPrice,
        rating: params.get('rating') || filters.rating
      };
      
      const response = await searchProperties(searchParams);
      if (response.success) {
        // Handle both paginated and non-paginated responses
        let propsData = response.properties;
        
        // If paginated, extract data array
        if (propsData && typeof propsData === 'object' && propsData.data) {
          propsData = propsData.data;
        }
        
        // Ensure it's an array
        const propertiesArray = Array.isArray(propsData) ? propsData : [];
        
        setProperties(propertiesArray);
        setTotalResults(response.total || propertiesArray.length || 0);
      }
    } catch (error) {
      console.error('Search failed:', error);
      setProperties([]);
      setTotalResults(0);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    
    // Reset validation errors
    const errors = {
      location: !searchData.location.trim(),
      checkIn: !searchData.checkIn,
      checkOut: !searchData.checkOut
    };
    
    setValidationErrors(errors);
    
    // Check if any errors
    if (errors.location || errors.checkIn || errors.checkOut) {
      return;
    }
    
    const params = new URLSearchParams({
      location: searchData.location,
      check_in: searchData.checkIn,
      check_out: searchData.checkOut,
      guests: searchData.guests,
      children: searchData.children || 0,
      rooms: searchData.rooms
    });
    navigate(`/?${params.toString()}`);
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0
    }).format(amount);
  };

  // If showing search results, render search results layout
  if (showResults) {
    return (
      <div className="search-results-wrapper">
        {/* Compact Search Bar */}
        <div className="search-bar-compact">
          <div className="container-wide">
            <form onSubmit={handleSearch} className="search-form-compact">
              <input
                type="text"
                value={searchData.location}
                onChange={(e) => setSearchData({...searchData, location: e.target.value})}
                placeholder="Where are you going?"
                className="search-input-compact"
              />
              <input
                type="date"
                value={searchData.checkIn}
                onChange={(e) => setSearchData({...searchData, checkIn: e.target.value})}
                className="search-input-compact"
                min={new Date().toISOString().split('T')[0]}
              />
              <input
                type="date"
                value={searchData.checkOut}
                onChange={(e) => setSearchData({...searchData, checkOut: e.target.value})}
                className="search-input-compact"
                min={searchData.checkIn || new Date().toISOString().split('T')[0]}
              />
              <div 
                className="search-input-compact search-field-clickable" 
                onClick={() => setShowGuestDropdown(!showGuestDropdown)}
                style={{ cursor: 'pointer', position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}
                ref={guestDropdownRef}
              >
                <span>{searchData.guests} adult{searchData.guests !== 1 ? 's' : ''} · {searchData.children} children · {searchData.rooms} room{searchData.rooms !== 1 ? 's' : ''}</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M7 10l5 5 5-5z"/>
                </svg>
                
                {/* Guest Dropdown */}
                {showGuestDropdown && (
                  <div className="occupancy-dropdown" onClick={(e) => e.stopPropagation()} style={{
                    position: 'absolute',
                    top: 'calc(100% + 8px)',
                    left: '0',
                    right: '0',
                    background: 'white',
                    border: '1px solid #ddd',
                    borderRadius: '8px',
                    boxShadow: '0 4px 16px rgba(0,0,0,0.2)',
                    padding: '20px',
                    minWidth: '320px',
                    zIndex: 9999
                  }}>
                    <div className="occupancy-row" style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      marginBottom: '20px'
                    }}>
                      <div className="occupancy-label">
                        <strong style={{fontSize: '15px'}}>Adults</strong>
                      </div>
                      <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '16px'}}>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, guests: Math.max(1, searchData.guests - 1)})}
                          disabled={searchData.guests <= 1}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.guests <= 1 ? '#f5f5f5' : 'white',
                            color: searchData.guests <= 1 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.guests <= 1 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          −
                        </button>
                        <span className="occupancy-value" style={{minWidth: '40px', textAlign: 'center', fontSize: '17px', fontWeight: '600'}}>{searchData.guests}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, guests: Math.min(20, searchData.guests + 1)})}
                          disabled={searchData.guests >= 20}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.guests >= 20 ? '#f5f5f5' : 'white',
                            color: searchData.guests >= 20 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.guests >= 20 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <div className="occupancy-row" style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      marginBottom: '20px'
                    }}>
                      <div className="occupancy-label">
                        <strong style={{fontSize: '15px'}}>Children</strong>
                      </div>
                      <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '16px'}}>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, children: Math.max(0, searchData.children - 1)})}
                          disabled={searchData.children <= 0}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.children <= 0 ? '#f5f5f5' : 'white',
                            color: searchData.children <= 0 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.children <= 0 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          −
                        </button>
                        <span className="occupancy-value" style={{minWidth: '40px', textAlign: 'center', fontSize: '17px', fontWeight: '600'}}>{searchData.children}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, children: Math.min(10, searchData.children + 1)})}
                          disabled={searchData.children >= 10}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.children >= 10 ? '#f5f5f5' : 'white',
                            color: searchData.children >= 10 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.children >= 10 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <div className="occupancy-row" style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'center',
                      marginBottom: '24px'
                    }}>
                      <div className="occupancy-label">
                        <strong style={{fontSize: '15px'}}>Rooms</strong>
                      </div>
                      <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '16px'}}>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, rooms: Math.max(1, searchData.rooms - 1)})}
                          disabled={searchData.rooms <= 1}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.rooms <= 1 ? '#f5f5f5' : 'white',
                            color: searchData.rooms <= 1 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.rooms <= 1 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          −
                        </button>
                        <span className="occupancy-value" style={{minWidth: '40px', textAlign: 'center', fontSize: '17px', fontWeight: '600'}}>{searchData.rooms}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, rooms: Math.min(10, searchData.rooms + 1)})}
                          disabled={searchData.rooms >= 10}
                          style={{
                            width: '36px',
                            height: '36px',
                            border: '2px solid #0071c2',
                            background: searchData.rooms >= 10 ? '#f5f5f5' : 'white',
                            color: searchData.rooms >= 10 ? '#ccc' : '#0071c2',
                            borderRadius: '50%',
                            cursor: searchData.rooms >= 10 ? 'not-allowed' : 'pointer',
                            fontSize: '20px',
                            fontWeight: 'bold',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.2s ease'
                          }}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <button
                      type="button"
                      onClick={() => setShowGuestDropdown(false)}
                      style={{
                        width: '100%',
                        padding: '12px',
                        background: '#0071c2',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontWeight: '600',
                        fontSize: '15px',
                        transition: 'all 0.2s ease'
                      }}
                      onMouseEnter={(e) => e.target.style.background = '#005999'}
                      onMouseLeave={(e) => e.target.style.background = '#0071c2'}
                    >
                      Done
                    </button>
                  </div>
                )}
              </div>
              <button type="submit" className="btn-search-compact">Search</button>
            </form>
          </div>
        </div>

        {/* Results Header */}
        <div className="results-header">
          <div className="container-wide">
            <div className="results-header-content">
              <h1 className="results-title">{searchData.location}: {totalResults} properties found</h1>
              <button 
                className="btn-mobile-filter"
                onClick={() => setShowFilterModal(true)}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>
                </svg>
                Filter
              </button>
            </div>
            <div className="results-sort">
              <button className="sort-btn active">Our top picks</button>
              <button className="sort-btn">Price (lowest first)</button>
              <button className="sort-btn">Best reviewed</button>
            </div>
          </div>
        </div>

        {/* Main Content: Filters + Results */}
        <div className="search-content">
          <div className="container-wide">
            <div className="search-layout">
              {/* Filters Sidebar */}
              <aside className={`filters-sidebar ${showFilterModal ? 'filters-sidebar-mobile-open' : ''}`}>
                <div className="filters-sidebar-header">
                  <h3 className="filter-title">Filter by:</h3>
                  <button 
                    className="btn-close-filters"
                    onClick={() => setShowFilterModal(false)}
                  >
                    ✕
                  </button>
                </div>

                {/* Price Filter */}
                <div className="filter-section">
                  <h4 
                    className="filter-heading filter-heading-clickable" 
                    onClick={() => toggleFilter('budget')}
                  >
                    Your budget (per night)
                    <svg 
                      width="16" 
                      height="16" 
                      viewBox="0 0 24 24" 
                      fill="currentColor"
                      style={{ 
                        transform: expandedFilters.budget ? 'rotate(180deg)' : 'rotate(0deg)',
                        transition: 'transform 0.3s ease'
                      }}
                    >
                      <path d="M7 10l5 5 5-5z"/>
                    </svg>
                  </h4>
                  {expandedFilters.budget && (
                    <div className="price-range">
                      <div className="price-labels-header">
                        <div className="price-label-item">
                          <span className="price-label-title">Minimum:</span>
                          <span className="price-label-value">${filters.minPrice}</span>
                        </div>
                        <div className="price-label-item">
                          <span className="price-label-title">Maximum:</span>
                          <span className="price-label-value">${filters.maxPrice.toLocaleString()}</span>
                        </div>
                      </div>
                      <div className="dual-slider-container">
                        <input
                          type="range"
                          min="0"
                          max="10000"
                          step="50"
                          value={filters.minPrice}
                          onChange={(e) => {
                            const value = parseInt(e.target.value);
                            if (value < filters.maxPrice) {
                              setFilters({...filters, minPrice: value});
                            }
                          }}
                          className="price-slider price-slider-min"
                        />
                        <input
                          type="range"
                          min="0"
                          max="10000"
                          step="50"
                          value={filters.maxPrice}
                          onChange={(e) => {
                            const value = parseInt(e.target.value);
                            if (value > filters.minPrice) {
                              setFilters({...filters, maxPrice: value});
                            }
                          }}
                          className="price-slider price-slider-max"
                        />
                      </div>
                      <div className="price-range-display">
                        ${filters.minPrice} - ${filters.maxPrice.toLocaleString()}
                      </div>
                      <button
                        onClick={() => applyFilters()}
                        className="btn-apply-filter"
                      >
                        Apply
                      </button>
                  </div>
                  )}
                </div>

                {/* Popular Filters */}
                <div className="filter-section">
                  <h4 
                    className="filter-heading filter-heading-clickable" 
                    onClick={() => toggleFilter('popular')}
                  >
                    Popular filters
                    <svg 
                      width="16" 
                      height="16" 
                      viewBox="0 0 24 24" 
                      fill="currentColor"
                      style={{ 
                        transform: expandedFilters.popular ? 'rotate(180deg)' : 'rotate(0deg)',
                        transition: 'transform 0.3s ease'
                      }}
                    >
                      <path d="M7 10l5 5 5-5z"/>
                    </svg>
                  </h4>
                  {expandedFilters.popular && (
                    <>
                      <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Hotels</span>
                    <span className="filter-count">234</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Apartments</span>
                    <span className="filter-count">156</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Free WiFi</span>
                    <span className="filter-count">432</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Free parking</span>
                    <span className="filter-count">287</span>
                  </label>
                  <label className="filter-checkbox">
                    <input type="checkbox" />
                    <span>Air conditioning</span>
                    <span className="filter-count">389</span>
                  </label>
                    </>
                  )}
                </div>

                {/* Guest Review Score */}
                <div className="filter-section">
                  <h4 
                    className="filter-heading filter-heading-clickable" 
                    onClick={() => toggleFilter('rating')}
                  >
                    Guest review score
                    <svg 
                      width="16" 
                      height="16" 
                      viewBox="0 0 24 24" 
                      fill="currentColor"
                      style={{ 
                        transform: expandedFilters.rating ? 'rotate(180deg)' : 'rotate(0deg)',
                        transition: 'transform 0.3s ease'
                      }}
                    >
                      <path d="M7 10l5 5 5-5z"/>
                    </svg>
                  </h4>
                  {expandedFilters.rating && (
                    <>
                      <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 0}
                      onChange={() => handleRatingChange(0)}
                    />
                    <span>Any rating</span>
                  </label>
                  <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 5}
                      onChange={() => handleRatingChange(5)}
                    />
                    <span>Excellent: 5</span>
                  </label>
                  <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 4}
                      onChange={() => handleRatingChange(4)}
                    />
                    <span>Very good: 4+</span>
                  </label>
                  <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 3}
                      onChange={() => handleRatingChange(3)}
                    />
                    <span>Good: 3+</span>
                  </label>
                  <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 2}
                      onChange={() => handleRatingChange(2)}
                    />
                    <span>Fair: 2+</span>
                  </label>
                  <label className="filter-checkbox">
                    <input 
                      type="radio" 
                      name="rating"
                      checked={filters.rating === 1}
                      onChange={() => handleRatingChange(1)}
                    />
                    <span>Poor: 1+</span>
                  </label>
                    </>
                  )}
                </div>
              </aside>

              {/* Property Listings */}
              <main className="results-main">
                {loading ? (
                  <div className="loading-results">
                    <div className="spinner-large"></div>
                    <p>Searching properties...</p>
                  </div>
                ) : properties.length > 0 ? (
                  <div className="property-list">
                    {properties.map(property => (
                      <div 
                        key={property.id} 
                        className="property-result-card"
                        onClick={() => navigate(`/property/${property.id}`)}
                        style={{ cursor: 'pointer' }}
                      >
                        <div className="property-result-image">
                          <img
                            src={property.image && !property.image.startsWith('http') 
                              ? `http://127.0.0.1:8000${property.image}` 
                              : property.image || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400'}
                            alt={property.name}
                            onError={(e) => {
                              console.error('Failed to load image:', property.image);
                              e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400';
                            }}
                          />
                          />
                          <button className="btn-wishlist-result" onClick={(e) => e.stopPropagation()}>♡</button>
                        </div>

                        <div className="property-result-content">
                          <div className="property-result-header">
                            <h3 className="property-result-name">{property.name}</h3>
                            {property.average_rating && (
                              <div className="property-result-rating">
                                <div className="rating-badge-result">
                                  <span className="rating-score-result">{property.average_rating.toFixed(1)}</span>
                                </div>
                                <div className="rating-text-result">
                                  <span className="rating-label-result">
                                    {property.average_rating >= 4.5 ? 'Excellent' : 
                                     property.average_rating >= 4.0 ? 'Very good' : 
                                     property.average_rating >= 3.0 ? 'Good' :
                                     property.average_rating >= 2.0 ? 'Fair' : 'Poor'}
                                  </span>
                                  <span className="rating-count-result">{property.review_count || 0} reviews</span>
                                </div>
                              </div>
                            )}
                          </div>

                          <p className="property-result-location">
                            📍 {property.city ? `${property.city}, ` : ''}{property.state ? `${property.state}, ` : ''}{property.country || property.location}
                          </p>

                          {property.description && (
                            <p className="property-result-desc">{property.description.substring(0, 150)}...</p>
                          )}
                        </div>

                        <div className="property-result-pricing">
                          {property.min_price && (
                            <>
                              <div className="price-label-result">Starting from</div>
                              <div className="price-amount-result">{formatCurrency(property.min_price)}</div>
                              <div className="price-taxes">per night</div>
                              <div className="price-taxes">Includes taxes and fees</div>
                              <button 
                                className="btn-see-avail-result"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  navigate(`/property/${property.id}`);
                                }}
                              >
                                See availability
                              </button>
                            </>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="no-results">
                    <h3>No properties found</h3>
                    <p>Try adjusting your search or filters</p>
                  </div>
                )}
              </main>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // Default: Show hero homepage
  return (
    <div className="home-page-wrapper">
      {/* Hero Section with Search */}
      <section className="hero-search-section">
        <div className="container">
          <div className="hero-search-content">
            <h1 className="hero-search-title">
              Start your next adventure<br />
              with the perfect hotel
            </h1>
            <p className="hero-search-subtitle">
              Explore scenic routes and hidden gems. Rent the<br />
              perfect hotel for your stay.
            </p>

            {/* Search Form Horizontal */}
            <form onSubmit={handleSearch} className="hero-search-form">
              <div className="search-field-group">
                <label className="search-field-label">Location</label>
                <input
                  type="text"
                  name="location"
                  value={searchData.location}
                  onChange={(e) => {
                    setSearchData({...searchData, location: e.target.value});
                    if (e.target.value.trim()) {
                      setValidationErrors({...validationErrors, location: false});
                    }
                  }}
                  placeholder="Any state"
                  className={`search-field-input ${validationErrors.location ? 'input-error' : ''}`}
                />
                {validationErrors.location && (
                  <span className="field-error-message">This field is required</span>
                )}
              </div>

              <div className="search-field-group">
                <label className="search-field-label">Date</label>
                <input
                  type="date"
                  name="checkIn"
                  value={searchData.checkIn}
                  onChange={(e) => {
                    setSearchData({...searchData, checkIn: e.target.value});
                    if (e.target.value) {
                      setValidationErrors({...validationErrors, checkIn: false});
                    }
                  }}
                  className={`search-field-input ${validationErrors.checkIn ? 'input-error' : ''}`}
                  min={new Date().toISOString().split('T')[0]}
                />
                {validationErrors.checkIn && (
                  <span className="field-error-message">This field is required</span>
                )}
              </div>

              <div className="search-field-group">
                <label className="search-field-label">Check-out</label>
                <input
                  type="date"
                  name="checkOut"
                  value={searchData.checkOut}
                  onChange={(e) => {
                    setSearchData({...searchData, checkOut: e.target.value});
                    if (e.target.value) {
                      setValidationErrors({...validationErrors, checkOut: false});
                    }
                  }}
                  className={`search-field-input ${validationErrors.checkOut ? 'input-error' : ''}`}
                  min={searchData.checkIn}
                />
                {validationErrors.checkOut && (
                  <span className="field-error-message">This field is required</span>
                )}
              </div>

              <div className="search-field-group search-field-occupancy">
                <label className="search-field-label">Guests & Rooms</label>
                <div 
                  className="search-field-input search-field-clickable" 
                  onClick={() => setShowGuestDropdown(!showGuestDropdown)}
                >
                  <span>{searchData.guests} adult{searchData.guests !== 1 ? 's' : ''} · {searchData.children} children · {searchData.rooms} room{searchData.rooms !== 1 ? 's' : ''}</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style={{marginLeft: 'auto'}}>
                    <path d="M7 10l5 5 5-5z"/>
                  </svg>
                </div>

                {/* Enhanced Guest Dropdown */}
                {showGuestDropdown && (
                  <div className="occupancy-dropdown" onClick={(e) => e.stopPropagation()}>
                    <div className="occupancy-row">
                      <div className="occupancy-label">
                        <strong>Adults</strong>
                      </div>
                      <div className="occupancy-controls">
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, guests: Math.max(1, searchData.guests - 1)})}
                          disabled={searchData.guests <= 1}
                        >
                          −
                        </button>
                        <span className="occupancy-value">{searchData.guests}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, guests: Math.min(20, searchData.guests + 1)})}
                          disabled={searchData.guests >= 20}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <div className="occupancy-row">
                      <div className="occupancy-label">
                        <strong>Children</strong>
                      </div>
                      <div className="occupancy-controls">
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, children: Math.max(0, searchData.children - 1)})}
                          disabled={searchData.children <= 0}
                        >
                          −
                        </button>
                        <span className="occupancy-value">{searchData.children}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, children: Math.min(10, searchData.children + 1)})}
                          disabled={searchData.children >= 10}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <div className="occupancy-row">
                      <div className="occupancy-label">
                        <strong>Rooms</strong>
                      </div>
                      <div className="occupancy-controls">
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, rooms: Math.max(1, searchData.rooms - 1)})}
                          disabled={searchData.rooms <= 1}
                        >
                          −
                        </button>
                        <span className="occupancy-value">{searchData.rooms}</span>
                        <button
                          type="button"
                          className="occupancy-btn"
                          onClick={() => setSearchData({...searchData, rooms: Math.min(10, searchData.rooms + 1)})}
                          disabled={searchData.rooms >= 10}
                        >
                          +
                        </button>
                      </div>
                    </div>

                    <button
                      type="button"
                      className="btn-occupancy-done"
                      onClick={() => setShowGuestDropdown(false)}
                    >
                      Done
                    </button>
                  </div>
                )}
              </div>

              <button type="submit" className="btn-search-hero">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                  <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
              </button>
            </form>
          </div>
        </div>
      </section>

      {/* Feature Cards Section */}
      <section className="feature-cards-section">
        <div className="container">
          <div className="feature-cards-grid">
            <div className="feature-card feature-card-large" style={{backgroundImage: 'url(https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600)'}}>
              <div className="feature-card-overlay"></div>
              <div className="feature-card-content">
                <h3 className="feature-card-title">More nature. More<br />memories.</h3>
                <button 
                  className="btn-feature-card"
                  onClick={() => navigate('/search')}
                >
                  Get started
                </button>
              </div>
            </div>

            <div className="feature-card feature-card-large" style={{backgroundImage: 'url(https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600)'}}>
              <div className="feature-card-overlay"></div>
              <div className="feature-card-content">
                <h3 className="feature-card-title">Find your freedom<br />in the Hotels</h3>
                <button 
                  className="btn-feature-card"
                  onClick={() => navigate('/search')}
                >
                  Explore Rooms
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Exclusive Deals Section */}
      {/* Removed - User requested to hide this section */}

      {/* Featured Properties Section */}
      {/* Removed - User requested to hide this section */}
    </div>
  );
};

export default HomePage;
