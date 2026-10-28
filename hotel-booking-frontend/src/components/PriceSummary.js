import React from 'react';
import { formatCurrency, formatDate, calculateNights } from '../utils/formatters';

/**
 * Price summary component
 * Displays booking details and pricing breakdown
 * 
 * @param {Object} booking - Booking details
 * @param {Object} pricing - Pricing breakdown including VAT
 * @param {Object} settings - Settings object with prices_include_vat flag
 */
const PriceSummary = ({ booking, pricing, settings }) => {
  if (!booking || !pricing) return null;

  const nights = calculateNights(booking.check_in, booking.check_out);
  
  // Determine if we should show VAT breakdown
  // If prices_include_vat is TRUE, hide the VAT line (it's already in the price)
  // If prices_include_vat is FALSE, show the breakdown
  const showVatBreakdown = settings && !settings.prices_include_vat;

  // Debug logging
  console.log('PriceSummary Debug:', {
    settings,
    prices_include_vat: settings?.prices_include_vat,
    showVatBreakdown,
    shouldShowNote: !showVatBreakdown,
    pricing_object: pricing,
    pricing_tax_rate: pricing?.tax_rate,
    pricing_tax_total: pricing?.tax_total,
    has_tax_rate: !!pricing?.tax_rate,
    has_tax_total: !!pricing?.tax_total
  });

  return (
    <div className="price-summary">
      <h3>Booking Summary</h3>

      <div className="summary-section">
        <h4>Stay Details</h4>
        <div className="detail-row">
          <span>Property:</span>
          <strong>{booking.property_name}</strong>
        </div>
        <div className="detail-row">
          <span>Room Type:</span>
          <strong>{booking.room_type_name}</strong>
        </div>
        <div className="detail-row">
          <span>Check-in:</span>
          <strong>{formatDate(booking.check_in)}</strong>
        </div>
        <div className="detail-row">
          <span>Check-out:</span>
          <strong>{formatDate(booking.check_out)}</strong>
        </div>
        <div className="detail-row">
          <span>Guests:</span>
          <strong>{booking.guests}</strong>
        </div>
        <div className="detail-row">
          <span>Rooms:</span>
          <strong>{booking.rooms || 1}</strong>
        </div>
        <div className="detail-row">
          <span>Nights:</span>
          <strong>{nights}</strong>
        </div>
      </div>

      <div className="summary-section">
        <h4>Price Breakdown</h4>
        
        <div className="detail-row">
          <span>{(booking.rooms || 1) > 1 ? `${nights} nights × ${booking.rooms} rooms` : `${nights} ${nights === 1 ? 'night' : 'nights'}`}</span>
          <strong>{formatCurrency(pricing.subtotal)}</strong>
        </div>

        {/* Show VAT breakdown only if it should be shown separately (prices exclude VAT) */}
        {showVatBreakdown && pricing.tax_rate && pricing.tax_total && (
          <div className="detail-row">
            <span>VAT ({pricing.tax_rate})</span>
            <strong>{formatCurrency(pricing.tax_total)}</strong>
          </div>
        )}

        {/* Total */}
        <div className="detail-row total">
          <span>Total</span>
          <strong>{formatCurrency(pricing.grand_total)} {pricing.currency}</strong>
        </div>

        {/* Note when VAT is included in prices */}
        {!showVatBreakdown && pricing.tax_rate && (
          <div className="vat-note">
            <small>* Includes VAT ({pricing.tax_rate})</small>
          </div>
        )}
      </div>

    </div>
  );
};

export default PriceSummary;
