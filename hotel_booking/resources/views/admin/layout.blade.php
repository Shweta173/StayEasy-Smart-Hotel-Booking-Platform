<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal') - Hotel Booking Platform</title>
    
    <!-- Toastr CSS for notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #262626;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #003580 0%, #00509e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" fill="%23ffffff"><path d="M320 200 L100 400 L100 850 L900 850 L900 400 L680 200 L650 230 L650 180 L550 180 L550 330 L500 380 L450 330 L450 180 L350 180 L350 230 Z M200 450 L250 450 L250 500 L200 500 Z M350 450 L400 450 L400 500 L350 500 Z M500 450 L550 450 L550 500 L500 500 Z M650 450 L700 450 L700 500 L650 500 Z M200 580 L250 580 L250 630 L200 630 Z M350 580 L400 580 L400 630 L350 630 Z M500 580 L550 580 L550 630 L500 630 Z M650 580 L700 580 L700 630 L650 630 Z M200 710 L250 710 L250 760 L200 760 Z M350 710 L400 710 L400 760 L350 760 Z M500 710 L600 710 L600 850 L400 850 L400 710 Z M650 710 L700 710 L700 760 L650 760 Z"/></svg>');
            background-size: 24px;
            background-repeat: no-repeat;
            background-position: center;
        }

        .sidebar-menu {
            padding: 16px 0;
        }

        .menu-section {
            margin-bottom: 24px;
        }

        .menu-section-title {
            padding: 12px 20px 8px;
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 600;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #febb02;
        }

        .menu-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu-text {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title-header {
            font-size: 24px;
            font-weight: 700;
            color: #262626;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .user-menu:hover {
            background: #f5f7fa;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003580 0%, #00509e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 16px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #262626;
        }

        .user-role {
            font-size: 12px;
            color: #6b6b6b;
            text-transform: capitalize;
        }

        .btn-logout {
            background: #dc2626;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #b91c1c;
        }

        /* Content Area */
        .content-area {
            padding: 32px;
            flex: 1;
        }

        /* Scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Slide-in Panel Styles */
        .panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .panel-overlay.active {
            display: block;
            opacity: 1;
        }

        .slide-panel {
            position: fixed;
            top: 0;
            right: -600px;
            width: 600px;
            max-width: 90vw;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
            z-index: 2001;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .slide-panel.active {
            right: 0;
        }

        .panel-header {
            padding: 24px 32px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
        }

        .panel-title {
            font-size: 20px;
            font-weight: 700;
            color: #262626;
        }

        .panel-close {
            width: 36px;
            height: 36px;
            border: none;
            background: #fee2e2;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #dc2626;
            transition: all 0.2s ease;
        }

        .panel-close:hover {
            background: #dc2626;
            color: white;
        }

        .panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 32px;
        }

        .panel-body::-webkit-scrollbar {
            width: 8px;
        }

        .panel-body::-webkit-scrollbar-track {
            background: #f5f7fa;
        }

        .panel-body::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .panel-body::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            padding: 20px 0;
            flex-wrap: wrap;
            gap: 16px;
        }

        .pagination-info {
            color: #6b6b6b;
            font-size: 14px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a,
        .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #262626;
            text-decoration: none;
            transition: all 0.2s ease;
            background: white;
        }

        .pagination a:hover {
            background: #f3f4f6;
            border-color: #0071c2;
            color: #0071c2;
        }

        .pagination .active span {
            background: #0071c2;
            color: white;
            border-color: #0071c2;
        }

        .pagination .disabled span {
            color: #9ca3af;
            cursor: not-allowed;
            background: #f9fafb;
        }

        .pagination .disabled span:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* Previous/Next arrows */
        .pagination li:first-child a,
        .pagination li:last-child a {
            font-size: 16px;
        }

        /* Form Validation Styles */
        .form-error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .form-error.show {
            display: block;
        }

        .form-group input.error,
        .form-group textarea.error,
        .form-group select.error {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .form-group input.error:focus,
        .form-group textarea.error:focus,
        .form-group select.error:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        /* Custom Confirm Dialog */
        .confirm-dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .confirm-dialog-overlay.show {
            display: flex;
        }

        .confirm-dialog {
            background: white;
            border-radius: 16px;
            padding: 40px 32px 32px;
            max-width: 440px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            animation: dialogFadeIn 0.2s ease;
        }

        @keyframes dialogFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .dialog-close-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 24px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .dialog-close-btn:hover {
            background: #f3f4f6;
            color: #262626;
        }

        .dialog-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            border: 4px solid #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 48px;
            font-weight: 300;
        }

        .confirm-dialog-title {
            font-size: 28px;
            font-weight: 600;
            color: #262626;
            margin-bottom: 16px;
        }

        .confirm-dialog-message {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .confirm-dialog-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirm-btn {
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 120px;
        }

        .confirm-btn-cancel {
            background: #d1d5db;
            color: #4b5563;
        }

        .confirm-btn-cancel:hover {
            background: #9ca3af;
        }

        .confirm-btn-delete {
            background: #ef4444;
            color: white;
        }

        .confirm-btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 4000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }

        .toast {
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            border-left: 4px solid;
            animation: toastSlideIn 0.3s ease;
            position: relative;
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast.toast-hiding {
            animation: toastSlideOut 0.3s ease;
        }

        @keyframes toastSlideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .toast-success {
            border-left-color: #16a34a;
        }

        .toast-error {
            border-left-color: #dc2626;
        }

        .toast-info {
            border-left-color: #0284c7;
        }

        .toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .toast-success .toast-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .toast-error .toast-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .toast-info .toast-icon {
            background: #e0f2fe;
            color: #0284c7;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #262626;
            margin-bottom: 2px;
        }

        .toast-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .toast-close:hover {
            background: #f3f4f6;
            color: #262626;
        }
    </style>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11?v={{ time() }}"></script>
    
    <style>
        /* Global SweetAlert2 z-index fix - Must be above all modals */
        .swal2-container {
            z-index: 999999 !important;
        }
        
        .swal2-popup {
            z-index: 1000000 !important;
        }

        /* Custom Logout Confirmation Styling */
        .logout-confirm-popup {
            border-radius: 16px !important;
            padding: 32px !important;
        }

        .logout-confirm-btn,
        .logout-cancel-btn {
            border-radius: 8px !important;
            padding: 12px 32px !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            min-width: 120px !important;
            transition: all 0.2s ease !important;
        }

        .logout-confirm-btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
        }

        .logout-cancel-btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3) !important;
        }

        /* Custom SweetAlert2 Login Success Styling */
        .swal-popup-custom {
            border-radius: 16px !important;
            padding: 32px !important;
        }

        .swal-popup-custom .swal2-icon {
            margin: 0 auto 20px !important;
        }

        .swal-popup-custom .swal2-title {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #262626 !important;
            margin-bottom: 12px !important;
        }

        .swal-popup-custom .swal2-html-container {
            font-size: 15px !important;
            color: #6b7280 !important;
        }

        .swal2-timer-progress-bar {
            background: #10b981 !important;
        }

        /* Custom Toastr Styling to match image - White background */
        #toast-container > .toast-success {
            background-color: #ffffff !important;
            color: #262626 !important;
            border-left: 4px solid #10b981 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        #toast-container > .toast-success:before {
            color: #10b981 !important;
        }

        #toast-container > .toast-error {
            background-color: #ffffff !important;
            color: #262626 !important;
            border-left: 4px solid #ef4444 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        #toast-container > .toast-error:before {
            color: #ef4444 !important;
        }

        #toast-container > .toast-info {
            background-color: #ffffff !important;
            color: #262626 !important;
            border-left: 4px solid #3b82f6 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        #toast-container > .toast-info:before {
            color: #3b82f6 !important;
        }

        #toast-container > .toast-warning {
            background-color: #ffffff !important;
            color: #262626 !important;
            border-left: 4px solid #f59e0b !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        #toast-container > .toast-warning:before {
            color: #f59e0b !important;
        }

        #toast-container > div {
            opacity: 1 !important;
            border-radius: 8px !important;
            padding: 16px 20px 16px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            min-width: 300px !important;
        }

        #toast-container .toast-title {
            font-weight: 700 !important;
            font-size: 15px !important;
            margin-bottom: 4px !important;
            color: #262626 !important;
        }

        #toast-container .toast-message {
            font-weight: 400 !important;
            color: #6b7280 !important;
        }

        #toast-container .toast-close-button {
            opacity: 0.5 !important;
            font-weight: 700 !important;
            color: #6b7280 !important;
        }

        #toast-container .toast-close-button:hover {
            opacity: 1 !important;
            color: #262626 !important;
        }

        #toast-container > div:before {
            font-size: 24px !important;
            line-height: 24px !important;
            margin-right: 12px !important;
            font-weight: 700 !important;
        }

        #toast-container .toast-progress {
            opacity: 0.3 !important;
            height: 4px !important;
        }

        #toast-container > .toast-success .toast-progress {
            background-color: #10b981 !important;
        }

        #toast-container > .toast-error .toast-progress {
            background-color: #ef4444 !important;
        }

        #toast-container > .toast-info .toast-progress {
            background-color: #3b82f6 !important;
        }

        #toast-container > .toast-warning .toast-progress {
            background-color: #f59e0b !important;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <div class="brand-icon"></div>
                    <span>Admin Portal</span>
                </a>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Main</div>
                    <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="menu-text">Dashboard</span>
                    </a>
                </div>

                @php
                    $userRole = session('admin_user')['role'] ?? session('admin_role') ?? 'front_desk';
                    $isAdmin = $userRole === 'admin';
                    $isManager = in_array($userRole, ['admin', 'manager']);
                    $isFrontDesk = in_array($userRole, ['admin', 'manager', 'front_desk']);
                @endphp

                @if($isManager)
                <div class="menu-section">
                    <div class="menu-section-title">Property Management</div>
                    <a href="{{ route('admin.properties') }}" class="menu-item {{ request()->routeIs('admin.properties') ? 'active' : '' }}">
                        <span class="menu-text">Properties</span>
                    </a>
                    <a href="{{ route('admin.room-types.index') }}" class="menu-item {{ request()->routeIs('admin.room-types*') ? 'active' : '' }}">
                        
                        <span class="menu-text">Room Types</span>
                    </a>
                    <a href="{{ route('admin.amenities') }}" class="menu-item {{ request()->routeIs('admin.amenities') ? 'active' : '' }}">
                        
                        <span class="menu-text">Amenities</span>
                    </a>
                    <a href="{{ route('admin.rate-plans') }}" class="menu-item {{ request()->routeIs('admin.rate-plans') ? 'active' : '' }}">
                        <span class="menu-text">Rate Plans / Pricing</span>
                    </a>
                    <a href="{{ route('admin.availability') }}" class="menu-item {{ request()->routeIs('admin.availability') ? 'active' : '' }}">
                        
                        <span class="menu-text">Availability Calendar</span>
                    </a>
                </div>
                @endif

                @if($isFrontDesk)
                <div class="menu-section">
                    <div class="menu-section-title">Reservations</div>
                    <a href="{{ route('admin.bookings') }}" class="menu-item {{ request()->routeIs('admin.bookings', 'admin.booking-detail') ? 'active' : '' }}">
                      
                        <span class="menu-text">Bookings / Reservations</span>
                    </a>
                </div>
                @endif

                @if($isManager)
                <div class="menu-section">
                    <div class="menu-section-title">Financial</div>
                    <a href="{{ route('admin.orders') }}" class="menu-item {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                        
                        <span class="menu-text">Orders</span>
                    </a>
                    <a href="{{ route('admin.invoices') }}" class="menu-item {{ request()->routeIs('admin.invoices') ? 'active' : '' }}">
                       
                        <span class="menu-text">Invoices</span>
                    </a>
                    <a href="{{ route('admin.payments') }}" class="menu-item {{ request()->routeIs('admin.payments') ? 'active' : '' }}">
                        
                        <span class="menu-text">Payments / Transactions</span>
                    </a>
                </div>
                @endif

                @if($isFrontDesk)
                <div class="menu-section">
                    <div class="menu-section-title">Customer Management</div>
                    <a href="{{ route('admin.guests') }}" class="menu-item {{ request()->routeIs('admin.guests') ? 'active' : '' }}">
                       
                        <span class="menu-text">Guests / Customers</span>
                    </a>
                    @if($isManager)
                    <a href="{{ route('admin.reviews') }}" class="menu-item {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                        
                        <span class="menu-text">Reviews</span>
                    </a>
                    @endif
                </div>
                @endif

                @if($isManager)
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    <a href="{{ route('admin.reports') }}" class="menu-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                       
                        <span class="menu-text">Reports / Analytics</span>
                    </a>
                    @if($isAdmin)
                    <a href="{{ route('admin.staff') }}" class="menu-item {{ request()->routeIs('admin.staff') ? 'active' : '' }}">
                        
                        <span class="menu-text">Staff / Users</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="menu-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        
                        <span class="menu-text">Settings</span>
                    </a>
                    @endif
                </div>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <h1 class="page-title-header">@yield('page-title', 'Dashboard')</h1>
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-avatar">
                            {{ strtoupper(substr(session('admin_user')['name'] ?? session('admin_name') ?? 'A', 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ session('admin_user')['name'] ?? session('admin_name') ?? 'Admin' }}</div>
                            <div class="user-role">{{ session('admin_user')['role'] ?? session('admin_role') ?? 'Admin' }}</div>
                        </div>
                    </div>
                    <form id="logoutForm" action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="button" class="btn-logout" onclick="showLogoutConfirmation()">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Slide-in Panel -->
    <div class="panel-overlay" id="panelOverlay"></div>
    <div class="slide-panel" id="slidePanel">
        <div class="panel-header">
            <h2 class="panel-title" id="panelTitle">Form</h2>
            <button class="panel-close" onclick="closeSlidePanel()">✕</button>
        </div>
        <div class="panel-body" id="panelBody">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

    <!-- Custom Confirm Dialog -->
    <div class="confirm-dialog-overlay" id="confirmDialog">
        <div class="confirm-dialog">
            <button class="dialog-close-btn" onclick="closeConfirmDialog()">×</button>
            <div class="dialog-icon">×</div>
            <div class="confirm-dialog-title">Are you sure?</div>
            <div class="confirm-dialog-message" id="confirmMessage">Do you really want to delete these records? This process cannot be undone.</div>
            <div class="confirm-dialog-buttons">
                <button class="confirm-btn confirm-btn-cancel" onclick="closeConfirmDialog()">Cancel</button>
                <button class="confirm-btn confirm-btn-delete" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            let icon, title;
            if (type === 'success') {
                icon = '✓';
                title = 'Success';
            } else if (type === 'error') {
                icon = '✕';
                title = 'Error';
            } else if (type === 'info') {
                icon = 'ℹ';
                title = 'Info';
            } else {
                icon = '✓';
                title = 'Success';
            }
            
            toast.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="removeToast(this)">×</button>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                removeToast(toast);
            }, 5000);
        }

        function removeToast(element) {
            const toast = element.classList ? element : element.parentElement.parentElement;
            toast.classList.add('toast-hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        // Check for toast messages in localStorage on page load
        document.addEventListener('DOMContentLoaded', function() {
            const toastMessage = localStorage.getItem('toastMessage');
            const toastType = localStorage.getItem('toastType');
            
            if (toastMessage) {
                showToast(toastMessage, toastType || 'success');
                localStorage.removeItem('toastMessage');
                localStorage.removeItem('toastType');
            }

            // Session flash messages are now handled by Toastr (below)
        });
        let confirmCallback = null;

        function showConfirmDialog(title, message, callback) {
            // Title is always "Are you sure?" so we don't need to update it
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmDialog').classList.add('show');
            confirmCallback = callback;
            
            document.getElementById('confirmDeleteBtn').onclick = function() {
                if (confirmCallback) confirmCallback();
                closeConfirmDialog();
            };
        }

        function closeConfirmDialog() {
            document.getElementById('confirmDialog').classList.remove('show');
            confirmCallback = null;
        }

        // Logout Confirmation Function
        function showLogoutConfirmation() {
            Swal.fire({
                title: 'Logout',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'logout-confirm-popup',
                    confirmButton: 'logout-confirm-btn',
                    cancelButton: 'logout-cancel-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the logout form
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        function openSlidePanel(url, title) {
            const overlay = document.getElementById('panelOverlay');
            const panel = document.getElementById('slidePanel');
            const panelTitle = document.getElementById('panelTitle');
            const panelBody = document.getElementById('panelBody');

            // Set title
            panelTitle.textContent = title;

            // Show loading
            panelBody.innerHTML = '<div style="text-align: center; padding: 48px;"><div style="font-size: 32px; margin-bottom: 16px;">⏳</div><div>Loading...</div></div>';

            // Show panel
            overlay.classList.add('active');
            panel.classList.add('active');

            // Load content via AJAX with proper headers
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(response => response.text())
                .then(html => {
                    panelBody.innerHTML = html;
                    // Re-initialize any scripts in the loaded content
                    executeScriptsInPanel();
                })
                .catch(error => {
                    panelBody.innerHTML = '<div style="text-align: center; padding: 48px; color: #dc2626;"><div style="font-size: 32px; margin-bottom: 16px;">⚠️</div><div>Failed to load form</div></div>';
                });
        }

        function closeSlidePanel() {
            const overlay = document.getElementById('panelOverlay');
            const panel = document.getElementById('slidePanel');
            
            overlay.classList.remove('active');
            panel.classList.remove('active');
        }

        function executeScriptsInPanel() {
            const panelBody = document.getElementById('panelBody');
            const scripts = panelBody.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript);
                document.body.removeChild(newScript);
            });
        }

        // Close panel on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSlidePanel();
            }
        });
    </script>

    <!-- jQuery and Toastr for toast notifications -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr options
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Check for Laravel session flash messages and show Toastr notifications
        $(document).ready(function() {
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            @if(session('info'))
                toastr.info('{{ session('info') }}');
            @endif

            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif
        });
    </script>

    @yield('scripts')
</body>
</html>
