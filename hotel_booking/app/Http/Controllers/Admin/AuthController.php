<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ============================================================
    // API AUTHENTICATION (JSON Responses)
    // ============================================================

    /**
     * API: Admin login (returns JSON with token)
     */
    public function login(Request $request)
    {
        $result = $this->authService->loginApi($request->all());
        
        return $this->successResponse($result, 'Login successful');
    }

    /**
     * API: Get authenticated admin user
     */
    public function me(Request $request)
    {
        $adminData = $this->authService->getAdminData($request->user());

        return $this->successResponse($adminData);
    }

    /**
     * API: Admin logout
     */
    public function logout(Request $request)
    {
        $this->authService->revokeToken($request->user());

        return $this->successResponse(null, 'Logged out successfully');
    }

    // ============================================================
    // WEB AUTHENTICATION (Redirects & Views)
    // ============================================================

    /**
     * Web: Show login form
     */
    public function showLogin()
    {
        return $this->authService->showLoginForm();
    }

    /**
     * Web: Handle login form submission
     */
    public function loginWeb(Request $request)
    {
        return $this->authService->loginWeb($request->all());
    }

    /**
     * Web: Handle logout
     */
    public function logoutWeb()
    {
        return $this->authService->logoutWeb();
    }
}
