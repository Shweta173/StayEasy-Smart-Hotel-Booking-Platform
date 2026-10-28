<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || $adminUser['role'] !== 'admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only administrators can access this resource.'
                ], 403);
            }
            
            abort(403, 'Unauthorized action. Only administrators can access this resource.');
        }
        
        return $next($request);
    }
}
