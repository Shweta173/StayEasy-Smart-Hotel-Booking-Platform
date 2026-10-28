<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hotel Booking Platform</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #0071c2 0%, #003580 100%);
            color: white;
            padding: 48px 40px 32px;
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 36px;
        }

        .login-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .login-form {
            padding: 40px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-error {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #0c0;
            border: 1px solid #cfc;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #262626;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0071c2;
            box-shadow: 0 0 0 4px rgba(0, 113, 194, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 16px 24px;
            background: #0071c2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #00509e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
        }

        .quick-login {
            padding: 32px 40px;
            border-top: 1px solid #e0e0e0;
        }

        .quick-login-title {
            font-size: 13px;
            font-weight: 600;
            color: #6b6b6b;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .quick-login-btn {
            width: 100%;
            padding: 12px 16px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            margin-bottom: 12px;
        }

        .quick-login-btn:hover {
            background: white;
            border-color: #0071c2;
            transform: translateX(4px);
        }

        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .role-admin { background: #e3f2fd; color: #0d47a1; }
        .role-manager { background: #f3e5f5; color: #4a148c; }
        .role-frontdesk { background: #e8f5e9; color: #1b5e20; }

        .role-email {
            font-size: 14px;
            color: #6b6b6b;
        }

        .login-footer {
            padding: 24px 40px;
            background: #f9fafb;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 13px;
            color: #6b6b6b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            
            <h1>Admin Portal</h1>
            <p>Hotel Booking Platform</p>
        </div>

        <form action="{{ route('admin.login.submit') }}" method="POST" class="login-form">
            @csrf

            @if(session('error'))
                <div class="alert alert-error">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@hotel.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <div class="quick-login">
            <p class="quick-login-title">Quick Login (Test Accounts)</p>
            <button type="button" class="quick-login-btn" onclick="quickLogin('admin@hotel.com', 'admin123')">
                <div class="role-badge role-admin">Admin</div>
                <div class="role-email">admin@hotel.com</div>
            </button>
            <button type="button" class="quick-login-btn" onclick="quickLogin('manager@hotel.com', 'manager123')">
                <div class="role-badge role-manager">Manager</div>
                <div class="role-email">manager@hotel.com</div>
            </button>
            <button type="button" class="quick-login-btn" onclick="quickLogin('frontdesk@hotel.com', 'frontdesk123')">
                <div class="role-badge role-frontdesk">Front Desk</div>
                <div class="role-email">frontdesk@hotel.com</div>
            </button>
        </div>

        <div class="login-footer">
            <p>© 2026 Hotel Booking Platform. All rights reserved.</p>
        </div>
    </div>

    <script>
        function quickLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
    
    <!-- jQuery (required for Toastr) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "4000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        
        // Show logout success toast if redirected with success message
        @if(session('success'))
            toastr.success('{{ session('success') }}', 'Success');
        @endif
        
        @if(session('error'))
            toastr.error('{{ session('error') }}', 'Error');
        @endif
    </script>
</body>
</html>
