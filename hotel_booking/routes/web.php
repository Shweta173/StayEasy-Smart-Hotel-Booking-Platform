<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\GuestController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\RatePlanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\AvailabilityController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Web Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Public routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.submit');
    
    // Protected routes
    Route::middleware(['admin.web.auth'])->group(function () {
        // Dashboard - All roles can access
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Bookings - All roles can VIEW, Admin & Manager can EDIT
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
        Route::get('/booking/{id}', [BookingController::class, 'show'])->name('booking-detail');
        
        // Check-in/Check-out - All roles can perform
        Route::post('/booking/{id}/check-in', [BookingController::class, 'checkIn'])->name('booking.check-in');
        Route::post('/booking/{id}/check-out', [BookingController::class, 'checkOut'])->name('booking.check-out');
        
        // Refund - Admin and Manager can process
        Route::post('/booking/{id}/refund', [BookingController::class, 'updateStatus'])->name('booking.refund');
        
        // Cancel booking - Admin & Manager only
        Route::middleware(['role.manager'])->group(function () {
            Route::post('/booking/{id}/cancel', [BookingController::class, 'updateStatus'])->name('booking.cancel');
        });
        
        // Guests - All roles can view
        Route::get('/guests', [GuestController::class, 'index'])->name('guests');
        Route::get('/guests/{id}', [GuestController::class, 'show'])->name('guest-detail');
        Route::get('/guests/{id}/bookings', [GuestController::class, 'bookings'])->name('guest-bookings');
        
        // ===== MANAGER & ADMIN ONLY ROUTES =====
        Route::middleware(['role.manager'])->group(function () {
            // Properties CRUD (Admin can delete, Manager can create/edit)
            Route::get('/properties', [PropertyController::class, 'index'])->name('properties');
            Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
            Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
            Route::get('/properties/{id}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
            Route::post('/properties/{id}', [PropertyController::class, 'update'])->name('properties.update');
            Route::post('/properties/images/{imageId}/delete', [PropertyController::class, 'deleteImage'])->name('properties.delete-image');
            Route::post('/properties/images/{imageId}/primary', [PropertyController::class, 'setPrimaryImage'])->name('properties.set-primary');
            Route::post('/properties/{id}/toggle-status', [PropertyController::class, 'toggleStatus'])->name('properties.toggle-status');
            
            // Room Types CRUD
            Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
            Route::get('/room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create');
            Route::post('/room-types', [RoomTypeController::class, 'store'])->name('room-types.store');
            Route::get('/room-types/{id}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit');
            Route::post('/room-types/{id}', [RoomTypeController::class, 'update'])->name('room-types.update');
            
            // Amenities CRUD
            Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities');
            Route::post('/amenities', [AmenityController::class, 'store'])->name('amenities.store');
            Route::post('/amenities/{id}', [AmenityController::class, 'update'])->name('amenities.update');
            
            // Rate Plans CRUD
            Route::get('/rate-plans', [RatePlanController::class, 'index'])->name('rate-plans');
            Route::get('/rate-plans/create', [RatePlanController::class, 'create'])->name('rate-plans.create');
            Route::post('/rate-plans', [RatePlanController::class, 'store'])->name('rate-plans.store');
            Route::get('/rate-plans/{id}/edit', [RatePlanController::class, 'edit'])->name('rate-plans.edit');
            Route::post('/rate-plans/{id}', [RatePlanController::class, 'update'])->name('rate-plans.update');
            
            // Availability Management
            Route::get('/availability', [AvailabilityController::class, 'calendar'])->name('availability');
            Route::post('/availability/block', [AvailabilityController::class, 'block'])->name('availability.block');
            Route::post('/availability/unblock/{id}', [AvailabilityController::class, 'unblock'])->name('availability.unblock');
            Route::post('/availability/update-block/{id}', [AvailabilityController::class, 'unblock'])->name('availability.update-block');
            
            // Financial Management
            Route::get('/orders', [OrderController::class, 'index'])->name('orders');
            
            // Invoices
            Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
            Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.view');
            Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
            Route::post('/invoices/{id}/email', [InvoiceController::class, 'sendEmail'])->name('invoices.email');
            Route::post('/invoices/{id}/mark-paid', [InvoiceController::class, 'show'])->name('invoices.mark-paid');
            
            // Payments & Refunds
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
            Route::post('/payments/{id}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
            
            // Reviews
            Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
            Route::post('/reviews/{id}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('/reviews/{id}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
            Route::get('/reviews/{id}/reply-form', [ReviewController::class, 'show'])->name('reviews.reply-form');
            Route::post('/reviews/{id}/reply', [ReviewController::class, 'addReply'])->name('reviews.reply');
            Route::get('/reviews/{id}/view', [ReviewController::class, 'show'])->name('reviews.view');
            
            // Reports
            Route::get('/reports', [OrderController::class, 'statistics'])->name('reports');
        });
        
        // ===== ADMIN ONLY ROUTES =====
        Route::middleware(['role.admin'])->group(function () {
            // Delete operations (Admin only)
            Route::post('/properties/{id}/delete', [PropertyController::class, 'destroy'])->name('properties.delete');
            Route::post('/room-types/{id}/delete', [RoomTypeController::class, 'destroy'])->name('room-types.delete');
            Route::post('/amenities/{id}/delete', [AmenityController::class, 'destroy'])->name('amenities.delete');
            Route::post('/rate-plans/{id}/delete', [RatePlanController::class, 'destroy'])->name('rate-plans.delete');
            Route::post('/invoices/{id}/void', [InvoiceController::class, 'void'])->name('invoices.void');
            
            // Staff Management (Admin only)
            Route::get('/staff', [StaffController::class, 'index'])->name('staff');
            Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.delete');
            
            // Settings (Admin only)
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.save');
        });
        
        Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
    });
});
