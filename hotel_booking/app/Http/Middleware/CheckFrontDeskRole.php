<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFrontDeskRole
{
    
    public function handle(Request $request, Closure $next)
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'manager', 'front_desk'])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Authentication required.'
                ], 403);
            }
            
            abort(403, 'Unauthorized action. Authentication required.');
        }
        
        return $next($request);
    }
}
