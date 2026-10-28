<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user() instanceof AdminUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in as admin.',
            ], 401);
        }

        return $next($request);
    }
}
