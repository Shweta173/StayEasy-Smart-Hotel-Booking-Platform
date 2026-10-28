<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
   
    public function loginApi(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $admin = AdminUser::where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        return [
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
        ];
    }

    
    public function getAdminData(AdminUser $admin): array
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
            'permissions' => [
                'can_manage_properties' => $admin->canManageProperties(),
                'can_manage_pricing' => $admin->canManagePricing(),
                'can_manage_bookings' => $admin->canManageBookings(),
            ],
        ];
    }

    
    public function revokeToken(AdminUser $admin): void
    {
        $admin->currentAccessToken()->delete();
    }

    public function showLoginForm()
    {
        if (Session::has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    
    public function loginWeb(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $admin = AdminUser::where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return back()->with('error', 'Invalid email or password')->withInput();
        }

        Session::put('admin_id', $admin->id);
        Session::put('admin_user', $admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Login successful! Welcome back, ' . $admin->name);
    }

    
    public function logoutWeb()
    {
        Session::flush();
        
        return redirect()->route('admin.login')
            ->with('success', 'Logged out successfully');
    }
}
