<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckManagerRole
{
    
    public function handle(Request $request, Closure $next)
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser || !in_array($adminUser['role'], ['admin', 'manager'])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Managers and administrators only.'
                ], 403);
            }
            
            abort(403, 'Unauthorized action. Managers and administrators only.');
        }
        
        return $next($request);
    }
}
