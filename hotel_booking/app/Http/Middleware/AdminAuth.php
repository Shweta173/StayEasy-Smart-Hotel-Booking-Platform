<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an AdminUser
        if (!$request->user() || !($request->user() instanceof AdminUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 401);
        }

        return $next($request);
    }
}

