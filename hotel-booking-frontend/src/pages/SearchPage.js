import React, { useState, useEffect, useCallback, useRef } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { searchProperties } from '../services/api';

/**
 * Page 2: Search Results / Property Listing
 * Displays properties matching search criteria with filters and sorting
 */
const SearchPage = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const guestDropdownRef = useRef(null);

  const [properties, setProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [totalResults, setTotalResults] = useState(0);
  const [showGuestDropdown, setShowGuestDropdown] = useState(false);

  // Search bar state (editable)
  const [searchData, setSearchData] = useState({
    location: searchParams.get('location') || '',
    checkIn:  searchParams.get('check_in')  || '',
    checkOut: searchParams.get('check_out') || '',
    guests:   parseInt(searchParams.get('guests'))   || 2,
    children: parseInt(searchParams.get('children')) || 0,
    rooms:    parseInt(searchParams.get('rooms'))    || 1,
  });

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

  // Filter state
  const [filters, setFilters] = useState({
    minPrice:   parseInt(searchParams.get('min_price')) || 0,
    maxPrice:   parseInt(searchParams.get('max_price')) || 10000,
    rating:     parseInt(searchParams.get('rating'))    || 0,
    amenities:  [],       // array of amenity names
    sortBy:     searchParams.get('sort_by')    || 'name',
    sortOrder:  searchParams.get('sort_order') || 'asc',
  });

  // Available amenities from backend
  const [availableAmenities, setAvailableAmenities] = useState([]);
  const [showAllAmenities, setShowAllAmenities] = useState(false);

  // Fetch amenities from backend
  useEffect(() => {
    const fetchAmenities = async () => {
      try {
        const response = await fetch('http://127.0.0.1:8000/api/public/amenities');
        const data = await response.json();
        console.log('Amenities API response:', data);
        
        // API response structure: { msgtype: 'success', data: { data: [...], current_page: ..., ... } }
        if (data.msgtype === 'success' && data.data) {
          // data.data contains paginated results, data.data.data is the actual array
          const amenitiesList = Array.isArray(data.data.data) ? data.data.data : [];
          console.log('Parsed amenities:', amenitiesList);
          setAvailableAmenities(amenitiesList.map(a => a.name));
        }
      } catch (error) {
        console.error('Failed to fetch amenities:', error);
        // Fallback to default amenities if fetch fails
        setAvailableAmenities(['Free WiFi', 'Free parking', 'Air conditioning', 'Pool', 'Restaurant']);
      }
    };
    fetchAmenities();
  }, []);

  // Determine which amenities to show
  const displayedAmenities = showAllAmenities ? availableAmenities : availableAmenities.slice(0, 5);
  const hasMoreAmenities = availableAmenities.length > 5;

  // ── Load results whenever URL params change ──────────────────────────────
  useEffect(() => {
    fetchResults({
      location:   searchParams.get('location')   || '',
      check_in:   searchParams.get('check_in')   || '',
      check_out:  searchParams.get('check_out')  || '',
      guests:     searchParams.get('guests')     || 2,
      rooms:      searchParams.get('rooms')      || 1,
      min_price:  searchParams.get('min_price')  || 0,
      max_price:  searchParams.get('max_price')  || 10000,
      rating:     searchParams.get('rating')     || 0,
      sort_by:    searchParams.get('sort_by')    || 'name',
      sort_order: searchParams.get('sort_order') || 'asc',
      amenities:  searchParams.get('amenities')  || '',
    });
  }, [searchParams]);

  const fetchResults = async (params) => {
    try {
      setLoading(true);
      const response = await searchProperties(params);
      if (response.success) {
        let propsData = response.properties;
        if (propsData && typeof propsData === 'object' && propsData.data) {
          propsData = propsData.data;
        }
        const arr = Array.isArray(propsData) ? propsData : [];
        setProperties(arr);
        setTotalResults(response.total || arr.length || 0);
      }
    } catch (err) {
      console.error('Search failed:', err);
      setProperties([]);
      setTotalResults(0);
    } finally {
      setLoading(false);
    }
  };

  // ── Push current filter state into URL → triggers useEffect above ────────
  const applyFilters = useCallback((overrides = {}) => {
    const merged = { ...filters, ...overrides };
    const params = new URLSearchParams({
      location:   searchData.location,
      check_in:   searchData.checkIn,
      check_out:  searchData.checkOut,
      guests:     searchData.guests,
      children:   searchData.children || 0,
      rooms:      searchData.rooms,
      min_price:  merged.minPrice,
      max_price:  merged.maxPrice,
      rating:     merged.rating,
      sort_by:    merged.sortBy,
      sort_order: merged.sortOrder,
    });
    if (merged.amenities.length > 0) {
      params.set('amenities', merged.amenities.join(','));
    }
    navigate(`/search?${params.toString()}`);
  }, [filters, searchData, navigate]);

  // ── Sort buttons ──────────────────────────────────────────────────────────
  const handleSort = (sortBy, sortOrder) => {
    const next = { ...filters, sortBy, sortOrder };
    setFilters(next);
    applyFilters({ sortBy, sortOrder });
  };

  // ── Price slider ──────────────────────────────────────────────────────────
  const handleMaxPriceChange = (val) => {
    setFilters(prev => ({ ...prev, maxPrice: val }));
  };

  // ── Rating radio ──────────────────────────────────────────────────────────
  const handleRatingChange = (val) => {
    const next = { ...filters, rating: val };
    setFilters(next);
    applyFilters({ rating: val });
  };

  // ── Amenity checkboxes ────────────────────────────────────────────────────
  const handleAmenityToggle = (name) => {
    const current = filters.amenities;
    const next = current.includes(name)
      ? current.filter(a => a !== name)
      : [...current, name];
    const nextFilters = { ...filters, amenities: next };
    setFilters(nextFilters);
    applyFilters({ amenities: next });
  };

  const formatCurrency = (amount) =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(amount);

  return (
    <div className="search-results-wrapper">

      {/* ── Search Bar ── */}
      <div className="search-bar-compact">
        <div className="container-wide">
          <div className="search-form-compact">
            <input
              type="text"
              value={searchData.location}
              onChange={(e) => setSearchData({ ...searchData, location: e.target.value })}
              placeholder="Where are you going?"
              className="search-input-compact"
            />
            <input
              type="date"
              value={searchData.checkIn}
              onChange={(e) => setSearchData({ ...searchData, checkIn: e.target.value })}
              className="search-input-compact"
              min={new Date().toISOString().split('T')[0]}
            />
            <input
              type="date"
              value={searchData.checkOut}
              onChange={(e) => setSearchData({ ...searchData, checkOut: e.target.value })}
              className="search-input-compact"
              min={searchData.checkIn || new Date().toISOString().split('T')[0]}
            />
            <div 
              className="search-input-compact search-field-clickable" 
              onClick={() => setShowGuestDropdown(!showGuestDropdown)}
              style={{ cursor: 'pointer', position: 'relative' }}
              ref={guestDropdownRef}
            >
              <span>{searchData.guests} adult{searchData.guests !== 1 ? 's' : ''} · {searchData.children} children · {searchData.rooms} room{searchData.rooms !== 1 ? 's' : ''}</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style={{marginLeft: 'auto', display: 'inline-block', verticalAlign: 'middle'}}>
                <path d="M7 10l5 5 5-5z"/>
              </svg>
              
              {/* Guest Dropdown */}
              {showGuestDropdown && (
                <div className="occupancy-dropdown" onClick={(e) => e.stopPropagation()} style={{
                  position: 'absolute',
                  top: '100%',
                  left: '0',
                  marginTop: '8px',
                  background: 'white',
                  border: '1px solid #ddd',
                  borderRadius: '8px',
                  boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                  padding: '16px',
                  minWidth: '280px',
                  zIndex: 1000
                }}>
                  <div className="occupancy-row" style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: '16px'
                  }}>
                    <div className="occupancy-label">
                      <strong>Adults</strong>
                    </div>
                    <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '12px'}}>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, guests: Math.max(1, searchData.guests - 1)})}
                        disabled={searchData.guests <= 1}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.guests <= 1 ? '#f5f5f5' : 'white',
                          color: searchData.guests <= 1 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.guests <= 1 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
                        }}
                      >
                        −
                      </button>
                      <span className="occupancy-value" style={{minWidth: '30px', textAlign: 'center', fontSize: '16px'}}>{searchData.guests}</span>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, guests: Math.min(20, searchData.guests + 1)})}
                        disabled={searchData.guests >= 20}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.guests >= 20 ? '#f5f5f5' : 'white',
                          color: searchData.guests >= 20 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.guests >= 20 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
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
                    marginBottom: '16px'
                  }}>
                    <div className="occupancy-label">
                      <strong>Children</strong>
                    </div>
                    <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '12px'}}>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, children: Math.max(0, searchData.children - 1)})}
                        disabled={searchData.children <= 0}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.children <= 0 ? '#f5f5f5' : 'white',
                          color: searchData.children <= 0 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.children <= 0 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
                        }}
                      >
                        −
                      </button>
                      <span className="occupancy-value" style={{minWidth: '30px', textAlign: 'center', fontSize: '16px'}}>{searchData.children}</span>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, children: Math.min(10, searchData.children + 1)})}
                        disabled={searchData.children >= 10}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.children >= 10 ? '#f5f5f5' : 'white',
                          color: searchData.children >= 10 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.children >= 10 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
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
                    marginBottom: '16px'
                  }}>
                    <div className="occupancy-label">
                      <strong>Rooms</strong>
                    </div>
                    <div className="occupancy-controls" style={{display: 'flex', alignItems: 'center', gap: '12px'}}>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, rooms: Math.max(1, searchData.rooms - 1)})}
                        disabled={searchData.rooms <= 1}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.rooms <= 1 ? '#f5f5f5' : 'white',
                          color: searchData.rooms <= 1 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.rooms <= 1 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
                        }}
                      >
                        −
                      </button>
                      <span className="occupancy-value" style={{minWidth: '30px', textAlign: 'center', fontSize: '16px'}}>{searchData.rooms}</span>
                      <button
                        type="button"
                        className="occupancy-btn"
                        onClick={() => setSearchData({...searchData, rooms: Math.min(10, searchData.rooms + 1)})}
                        disabled={searchData.rooms >= 10}
                        style={{
                          width: '32px',
                          height: '32px',
                          border: '1px solid #0071c2',
                          background: searchData.rooms >= 10 ? '#f5f5f5' : 'white',
                          color: searchData.rooms >= 10 ? '#ccc' : '#0071c2',
                          borderRadius: '50%',
                          cursor: searchData.rooms >= 10 ? 'not-allowed' : 'pointer',
                          fontSize: '18px',
                          fontWeight: 'bold'
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
                      padding: '10px',
                      background: '#0071c2',
                      color: 'white',
                      border: 'none',
                      borderRadius: '4px',
                      cursor: 'pointer',
                      fontWeight: '600'
                    }}
                  >
                    Done
                  </button>
                </div>
              )}
            </div>
            <button onClick={() => applyFilters()} className="btn-search-compact">Search</button>
          </div>
        </div>
      </div>

      {/* ── Results Header ── */}
      <div className="results-header">
        <div className="container-wide">
          <h1 className="results-title">
            {searchData.location ? `${searchData.location}: ` : 'Search Results: '}
            {totalResults} {totalResults === 1 ? 'property' : 'properties'} found
          </h1>
          <div className="results-sort">
            <button
              className={`sort-btn ${filters.sortBy === 'name' ? 'active' : ''}`}
              onClick={() => handleSort('name', 'asc')}
            >Our top picks</button>
            <button
              className={`sort-btn ${filters.sortBy === 'price' ? 'active' : ''}`}
              onClick={() => handleSort('price', 'asc')}
            >Price (lowest first)</button>
            <button
              className={`sort-btn ${filters.sortBy === 'rating' ? 'active' : ''}`}
              onClick={() => handleSort('rating', 'desc')}
            >Best reviewed</button>
          </div>
        </div>
      </div>

      {/* ── Main Content ── */}
      <div className="search-content">
        <div className="container-wide">
          <div className="search-layout">

            {/* ── Filters Sidebar ── */}
            <aside className="filters-sidebar">
              <h3 className="filter-title">Filter by:</h3>

              {/* Budget */}
              <div className="filter-section">
                <h4 className="filter-heading">Your budget (per night)</h4>
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
                          setFilters(prev => ({ ...prev, minPrice: value }));
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
                          handleMaxPriceChange(value);
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
              </div>

              {/* Guest Review Score */}
              <div className="filter-section">
                <h4 className="filter-heading">Guest review score</h4>
                {[
                  { label: 'Any rating',     value: 0 },
                  { label: 'Excellent: 5',   value: 5 },
                  { label: 'Very good: 4+',  value: 4 },
                  { label: 'Good: 3+',       value: 3 },
                  { label: 'Fair: 2+',       value: 2 },
                  { label: 'Poor: 1+',       value: 1 },
                ].map(({ label, value }) => (
                  <label key={value} className="filter-checkbox">
                    <input
                      type="radio"
                      name="rating"
                      checked={filters.rating === value}
                      onChange={() => handleRatingChange(value)}
                    />
                    <span>{label}</span>
                  </label>
                ))}
              </div>

              {/* Amenities */}
              <div className="filter-section">
                <h4 className="filter-heading">Amenities</h4>
                {displayedAmenities.map((name) => (
                  <label key={name} className="filter-checkbox">
                    <input
                      type="checkbox"
                      checked={filters.amenities.includes(name)}
                      onChange={() => handleAmenityToggle(name)}
                    />
                    <span>{name}</span>
                  </label>
                ))}
                {hasMoreAmenities && (
                  <button
                    className="btn-view-more-amenities"
                    onClick={() => setShowAllAmenities(!showAllAmenities)}
                  >
                    {showAllAmenities ? '- View Less' : '+ View More'}
                  </button>
                )}
              </div>
            </aside>

            {/* ── Property Listings ── */}
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
                          src={
                            property.image && !property.image.startsWith('http')
                              ? `http://127.0.0.1:8000${property.image}`
                              : property.image || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400'
                          }
                          alt={property.name}
                          onError={(e) => {
                            console.error('Failed to load image:', property.image);
                            e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400';
                          }}
                        />
                        <button 
                          className="btn-wishlist-result"
                          onClick={(e) => e.stopPropagation()}
                        >
                          ♡
                        </button>
                      </div>

                      <div className="property-result-content">
                        <div className="property-result-header">
                          <h3 className="property-result-name">{property.name}</h3>
                          {property.average_rating > 0 && (
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
                                <span className="rating-count-result">{property.total_reviews || 0} reviews</span>
                              </div>
                            </div>
                          )}
                        </div>

                        <p className="property-result-location">
                          📍 {property.city ? `${property.city}${property.state ? `, ${property.state}` : ''}${property.country ? `, ${property.country}` : ''}` : property.location || 'Location not specified'}
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
                <div className="not-found-page" style={{minHeight: '60vh', background: 'transparent'}}>
                  <button className="back-button-404" onClick={() => navigate(-1)}>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                  </button>
                  <div className="not-found-content">
                    <h1 className="error-code" style={{fontSize: '80px'}}>🔍</h1>
                    <h2 className="error-title">No Properties Found</h2>
                    <p className="error-description">
                      Try adjusting your search criteria or filters to find available properties.
                    </p>
                  </div>
                </div>
              )}
            </main>

          </div>
        </div>
      </div>
    </div>
  );
};

export default SearchPage;
