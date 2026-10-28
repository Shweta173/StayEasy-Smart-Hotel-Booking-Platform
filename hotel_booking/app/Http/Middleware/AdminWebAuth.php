<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\AdminUser;

class AdminWebAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('admin.login')->with('error', 'Please login to continue');
        }

        // Ensure admin_user object is in session (for backward compatibility)
        if (!Session::has('admin_user')) {
            $admin = AdminUser::find(Session::get('admin_id'));
            if ($admin) {
                Session::put('admin_user', $admin);
            } else {
                Session::flush();
                return redirect()->route('admin.login')->with('error', 'Session expired. Please login again.');
            }
        }

        return $next($request);
    }
}
