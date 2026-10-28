import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getInvoiceData, downloadInvoicePDF } from '../services/api';
import './InvoicePage.css';

/**
 * Invoice Page (Page 10)
 * 
 * Shows invoice details and allows PDF download
 * Can be accessed with or without authentication (using invoice number)
 */
function InvoicePage() {
  const { invoiceNumber } = useParams();
  const navigate = useNavigate();
  const [invoice, setInvoice] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [downloading, setDownloading] = useState(false);

  useEffect(() => {
    loadInvoice();
  }, [invoiceNumber]);

  const loadInvoice = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await getInvoiceData(invoiceNumber);
      
      if (response.success) {
        setInvoice(response.data);
      } else {
        setError(response.message || 'Failed to load invoice');
      }
    } catch (err) {
      console.error('Error loading invoice:', err);
      setError(err.message || 'Failed to load invoice');
    } finally {
      setLoading(false);
    }
  };

  const handleDownloadPDF = async () => {
    try {
      setDownloading(true);
      
      const blob = await downloadInvoicePDF(invoiceNumber);
      
      // Create download link
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `Invoice-${invoiceNumber}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
    } catch (err) {
      console.error('Error downloading PDF:', err);
      alert('Failed to download invoice PDF');
    } finally {
      setDownloading(false);
    }
  };

  const handlePrint = () => {
    window.print();
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading-container">
          <div className="spinner"></div>
          <p>Loading invoice...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="page-container">
        <div className="error-container">
          <div className="error-icon">⚠️</div>
          <h2>Invoice Not Found</h2>
          <p>{error}</p>
          <button 
            className="btn-primary"
            onClick={() => navigate('/my-bookings')}
          >
            ← Back to My Bookings
          </button>
        </div>
      </div>
    );
  }

  if (!invoice) {
    return null;
  }

  return (
    <div className="page-container invoice-page">
      {/* Action Buttons (no-print) */}
      <div className="invoice-actions no-print">
        <button 
          className="btn-back"
          onClick={() => navigate(-1)}
        >
          ← Back
        </button>
        <div className="invoice-right-actions">
          <button 
            className="btn-action-primary"
            onClick={handlePrint}
          >
            Print
          </button>
          <button 
            className="btn-action-primary"
            onClick={handleDownloadPDF}
            disabled={downloading}
          >
            {downloading ? 'Downloading...' : 'Download PDF'}
          </button>
        </div>
      </div>

      {/* Invoice Document */}
      <div className="invoice-document">
        {/* Invoice Header */}
        <div className="invoice-header">
          <div className="company-info">
            <h1> Hotel Booking Platform</h1>
            <p>Professional Hotel Reservation Services</p>
          </div>
          <div className="invoice-title">
            <h2>INVOICE</h2>
            <p className="invoice-number">{invoice.invoice_number}</p>
          </div>
        </div>

        {/* Invoice Info */}
        <div className="invoice-info-section">
          <div className="invoice-dates">
            <div className="info-block">
              <span className="label">Issue Date:</span>
              <span className="value">{formatDate(invoice.issued_date)}</span>
            </div>
            <div className="info-block">
              <span className="label">Due Date:</span>
              <span className="value">{formatDate(invoice.due_date)}</span>
            </div>
            <div className="info-block">
              <span className="label">Status:</span>
              <span className={`invoice-status status-${invoice.status}`}>
                {invoice.status.toUpperCase()}
              </span>
            </div>
          </div>
        </div>

        {/* Bill To / Property Info */}
        <div className="invoice-parties">
          <div className="party-block">
            <h3>Bill To:</h3>
            <p><strong>{invoice.guest.first_name} {invoice.guest.last_name}</strong></p>
            <p>{invoice.guest.email}</p>
            <p>{invoice.guest.phone}</p>
          </div>
          <div className="party-block">
            <h3>Property:</h3>
            <p><strong>{invoice.property.name}</strong></p>
            <p>{invoice.property.address}</p>
            <p>{invoice.property.city}, {invoice.property.state} {invoice.property.postal_code}</p>
            <p>{invoice.property.country}</p>
          </div>
        </div>

        {/* Booking Details */}
        <div className="booking-summary">
          <h3>Booking Details</h3>
          <div className="summary-grid">
            <div className="summary-item">
              <span className="label">Booking Reference:</span>
              <span className="value">{invoice.booking.booking_reference}</span>
            </div>
            <div className="summary-item">
              <span className="label">Room Type:</span>
              <span className="value">{invoice.booking.room_type}</span>
            </div>
            <div className="summary-item">
              <span className="label">Check-in:</span>
              <span className="value">{formatDate(invoice.booking.check_in_date)}</span>
            </div>
            <div className="summary-item">
              <span className="label">Check-out:</span>
              <span className="value">{formatDate(invoice.booking.check_out_date)}</span>
            </div>
            <div className="summary-item">
              <span className="label">Nights:</span>
              <span className="value">{invoice.booking.nights}</span>
            </div>
            <div className="summary-item">
              <span className="label">Guests:</span>
              <span className="value">{invoice.booking.guests}</span>
            </div>
          </div>
        </div>

        {/* Line Items Table */}
        <div className="invoice-items">
          <table>
            <thead>
              <tr>
                <th>Description</th>
                <th className="text-center">Quantity</th>
                <th className="text-right">Unit Price</th>
                <th className="text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              {invoice.items.map((item, index) => (
                <tr key={index}>
                  <td>{item.description}</td>
                  <td className="text-center">{item.quantity}</td>
                  <td className="text-right">{formatAmount(item.unit_price)}</td>
                  <td className="text-right">{formatAmount(item.total_price)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Totals */}
        <div className="invoice-totals">
          <div className="totals-table">
            <div className="total-row">
              <span className="label">Subtotal:</span>
              <span className="value">{formatAmount(invoice.subtotal)}</span>
            </div>
            {invoice.tax_amount > 0 && (
              <div className="total-row">
                <span className="label">Taxes:</span>
                <span className="value">{formatAmount(invoice.tax_amount)}</span>
              </div>
            )}
            {invoice.service_fee > 0 && (
              <div className="total-row">
                <span className="label">Service Fee:</span>
                <span className="value">{formatAmount(invoice.service_fee)}</span>
              </div>
            )}
            <div className="total-row grand-total">
              <span className="label">Grand Total:</span>
              <span className="value">{formatAmount(invoice.total_amount)}</span>
            </div>
          </div>
        </div>

        {/* Payment Information */}
        {invoice.payments && invoice.payments.length > 0 && (
          <div className="payment-info-section">
            <h3>Payment Information</h3>
            {invoice.payments.map((payment, index) => (
              <div key={index} className="payment-record">
                <div className="payment-detail">
                  <span className="label">Payment Method:</span>
                  <span className="value">{payment.payment_method}</span>
                </div>
                <div className="payment-detail">
                  <span className="label">Transaction ID:</span>
                  <span className="value">{payment.transaction_id}</span>
                </div>
                <div className="payment-detail">
                  <span className="label">Amount Paid:</span>
                  <span className="value">{formatAmount(payment.amount)}</span>
                </div>
                <div className="payment-detail">
                  <span className="label">Payment Date:</span>
                  <span className="value">{formatDate(payment.paid_at)}</span>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Footer Notes */}
        <div className="invoice-footer">
          <div className="footer-notes">
            <p><strong>Notes:</strong></p>
            <p>Thank you for choosing our hotel booking platform. We hope you enjoy your stay!</p>
            <p>For any inquiries regarding this invoice, please contact the property directly.</p>
          </div>
          <div className="footer-legal">
            <p className="text-muted">
              This is a computer-generated invoice and does not require a signature.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default InvoicePage;
