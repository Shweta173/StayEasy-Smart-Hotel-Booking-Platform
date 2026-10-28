<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\RatePlanController;
use App\Http\Controllers\Admin\AmenityController;
use App\Models\Guest;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Payment;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Admin CMS Routes
Route::prefix('admin')->group(function () {
    
    // Public auth routes
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected admin routes
    Route::middleware(['auth:sanctum', 'admin.auth'])->group(function () {
        
        // Auth
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Dashboard (all roles)
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
        // Properties (admin & manager only)
        Route::middleware(['admin.role:admin,manager'])->group(function () {
            // Properties CRUD
            Route::apiResource('properties', PropertyController::class);
            
            // Property Images - handled by PropertyController
            Route::post('properties/{id}/images', [PropertyController::class, 'uploadImages']);
            Route::post('properties/images/{imageId}/primary', [PropertyController::class, 'setPrimaryImage']);
            Route::delete('properties/images/{imageId}', [PropertyController::class, 'deleteImage']);
            
            // Room Types CRUD
            Route::apiResource('room-types', RoomTypeController::class);
            
            // Rate Plans CRUD
            Route::apiResource('rate-plans', RatePlanController::class);
            Route::get('rate-plans/calendar/pricing', [RatePlanController::class, 'calendar']);
        });
        
        // Amenities (admin only for create/update/delete, all can view)
        Route::get('amenities', [AmenityController::class, 'index']);
        Route::get('amenities/{amenity}', [AmenityController::class, 'show']);
        
        Route::middleware(['admin.role:admin'])->group(function () {
            Route::post('amenities', [AmenityController::class, 'store']);
            Route::put('amenities/{amenity}', [AmenityController::class, 'update']);
            Route::delete('amenities/{amenity}', [AmenityController::class, 'destroy']);
        });
        
        // Bookings (all roles can access)
        Route::get('bookings', [App\Http\Controllers\Admin\BookingController::class, 'index']);
        Route::get('bookings/statistics', [App\Http\Controllers\Admin\BookingController::class, 'statistics']);
        Route::get('bookings/today', [App\Http\Controllers\Admin\BookingController::class, 'todayActivity']);
        Route::get('bookings/{id}', [App\Http\Controllers\Admin\BookingController::class, 'show']);
        Route::post('bookings/{id}/check-in', [App\Http\Controllers\Admin\BookingController::class, 'checkIn']);
        Route::post('bookings/{id}/check-out', [App\Http\Controllers\Admin\BookingController::class, 'checkOut']);
        
        // Admin and Manager can update booking status
        Route::middleware(['admin.role:admin,manager'])->group(function () {
            Route::put('bookings/{id}/status', [App\Http\Controllers\Admin\BookingController::class, 'updateStatus']);
        });
        
        // Orders & Invoices (all roles can access)
        Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index']);
        Route::get('orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show']);
        Route::get('invoices', [App\Http\Controllers\Admin\InvoiceController::class, 'index']);
        Route::get('invoices/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'show']);
        
        // Reviews Management (all roles can view)
        Route::get('reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index']);
        Route::get('reviews/{id}', [App\Http\Controllers\Admin\ReviewController::class, 'show']);
        
        // Booking Holds Management
        Route::get('holds', [App\Http\Controllers\Admin\AvailabilityController::class, 'listHolds']);
        Route::post('holds/{id}/release', [App\Http\Controllers\Admin\AvailabilityController::class, 'releaseHold']);
        Route::post('holds/cleanup-expired', [App\Http\Controllers\Admin\AvailabilityController::class, 'cleanupExpiredHolds']);
        
        // Admin and Manager can approve/reject reviews and add replies
        Route::middleware(['admin.role:admin,manager'])->group(function () {
            Route::post('reviews/{id}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve']);
            Route::post('reviews/{id}/reject', [App\Http\Controllers\Admin\ReviewController::class, 'reject']);
            Route::post('reviews/{id}/reply', [App\Http\Controllers\Admin\ReviewController::class, 'addReply']);
        });
        
        // Admin only can delete reviews
        Route::middleware(['admin.role:admin'])->group(function () {
            Route::delete('reviews/{id}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy']);
        });
    });
});

// Guest Booking Engine Routes (Public API)
Route::prefix('public')->group(function () {
    
    // Settings (for VAT display logic)
    Route::get('settings', [App\Http\Controllers\Public\SettingsController::class, 'index']);
    
    // Amenities (for filter display)
    Route::get('amenities', [AmenityController::class, 'index']);
    
    // Frontend Page 1: Home / Landing - Featured properties
    Route::get('properties/featured', [App\Http\Controllers\Public\PropertyController::class, 'featured']);
    
    // Frontend Page 2: Search Results - Property listing with filters
    Route::get('properties/search', [App\Http\Controllers\Public\PropertyController::class, 'search']);
    
    // Frontend Page 3: Property Detail - Full property info
    Route::get('properties/{id}', [App\Http\Controllers\Public\PropertyController::class, 'show']);
    
    // Frontend Page 4: Room Selection / Availability
    Route::post('availability/check', [App\Http\Controllers\Public\AvailabilityController::class, 'check']);
    Route::get('availability/calendar', [App\Http\Controllers\Public\AvailabilityController::class, 'calendar']);
    Route::post('availability/search', [App\Http\Controllers\Public\AvailabilityController::class, 'search']);
    
    // Hold Management (cleanup expired holds)
    Route::post('holds/cleanup', [App\Http\Controllers\Public\AvailabilityController::class, 'cleanupHolds']);
    
    // Frontend Page 5: Checkout - Pricing & Order Creation
    Route::post('bookings/pricing', [App\Http\Controllers\Public\BookingController::class, 'getPricing']);
    
    // AUTHENTICATION REQUIRED FOR BOOKING
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('bookings/hold', [App\Http\Controllers\Public\BookingController::class, 'createHold']);
        Route::get('bookings/hold/{id}/status', [App\Http\Controllers\Public\BookingController::class, 'checkHoldStatus']);
        
        // Frontend Page 5: Payment - Stripe Integration
        Route::post('payment/intent', [App\Http\Controllers\Public\PaymentController::class, 'createPaymentIntent']);
        Route::get('payment/status/{paymentIntentId}', [App\Http\Controllers\Public\PaymentController::class, 'checkPaymentStatus']);
    });
    
    // Cancel Booking (Requires authentication)
    Route::middleware('auth:sanctum')->post('bookings/{id}/cancel', [App\Http\Controllers\Public\BookingController::class, 'cancelBooking']);
    
    // Frontend Page 10: Invoice / Receipt
    Route::get('invoices/{invoiceNumber}', [App\Http\Controllers\Public\InvoiceController::class, 'show']);
    Route::get('invoices/{invoiceNumber}/download', [App\Http\Controllers\Public\InvoiceController::class, 'download']);
    
    // Booking Lookup (for guests without account)
    Route::post('bookings/lookup', [App\Http\Controllers\Public\BookingController::class, 'lookupBooking']);
    Route::get('bookings/reference/{reference}', [App\Http\Controllers\Public\BookingController::class, 'getByReference']);
    
    // Frontend Page 8: My Bookings - Search by email (no auth required - for backward compatibility)
    Route::post('bookings/search', [App\Http\Controllers\Public\BookingController::class, 'searchBookingsByEmail']);
    
    // Frontend Page 7: Guest Authentication
    Route::post('guest/register', [App\Http\Controllers\Public\GuestAuthController::class, 'register']);
    Route::post('guest/login', [App\Http\Controllers\Public\GuestAuthController::class, 'login']);
    
    // Protected Guest Routes (Requires guest authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('guest/logout', [App\Http\Controllers\Public\GuestAuthController::class, 'logout']);
        Route::get('guest/profile', [App\Http\Controllers\Public\GuestAuthController::class, 'profile']);
        
        // Frontend Page 8: My Bookings (Authenticated - shows all user's bookings)
        Route::get('guest/bookings', [App\Http\Controllers\Public\GuestAuthController::class, 'myBookings']);
        
        // Frontend Page 9: Booking Detail
        Route::get('guest/bookings/{id}', [App\Http\Controllers\Public\BookingController::class, 'show']);
    });
    
    // Reviews (Public)
    Route::post('reviews', [App\Http\Controllers\Public\ReviewController::class, 'store']);
    Route::get('reviews', [App\Http\Controllers\Public\ReviewController::class, 'index']);
    Route::get('properties/{propertyId}/reviews', [App\Http\Controllers\Public\ReviewController::class, 'getPropertyReviews']);
});

// Stripe Webhook (No CSRF protection needed)
Route::post('webhooks/stripe', [App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

