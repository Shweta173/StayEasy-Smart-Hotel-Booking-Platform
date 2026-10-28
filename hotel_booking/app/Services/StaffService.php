<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StaffService
{
    use ApiResponse;
  
    public function index(Request $request)
    {
        $staffMembers = $this->list();
        $stats = $this->getStatistics();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'staff' => $staffMembers,
                'stats' => $stats
            ], 'Staff retrieved successfully');
        }

        // Return view for web requests
        return view('admin.staff', compact('staffMembers', 'stats'));
    }
   
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admin_users,email',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:admin,manager,staff,front_desk',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $staff = $this->createStaff($request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->createdResponse($staff, 'Staff member created successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.staff')
                ->with('success', 'Staff member created successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
 
    public function update(Request $request, $id)
    {
        try {
            // Build validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admin_users,email,' . $id,
                'role' => 'required|in:admin,manager,staff,front_desk',
            ];

            // Only add password validation if password is provided
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:8|confirmed';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $staff = $this->updateStaff($id, $request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($staff, 'Staff member updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.staff')
                ->with('success', 'Staff member updated successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    public function destroy(Request $request, $id)
    {
        try {
            // Prevent deleting yourself
            if (auth()->guard('admin')->id() == $id) {
                throw new \Exception('You cannot delete your own account');
            }

            $this->deleteStaff($id);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->deletedResponse('Staff member deleted successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.staff')
                ->with('success', 'Staff member deleted successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function list()
    {
        return AdminUser::orderBy('created_at', 'desc')->get();
    }

    protected function getStatistics(): array
    {
        return [
            'admin' => AdminUser::where('role', 'admin')->count(),
            'manager' => AdminUser::where('role', 'manager')->count(),
            'front_desk' => AdminUser::whereIn('role', ['staff', 'front_desk'])->count(),
            'total' => AdminUser::count(),
        ];
    }

    protected function createStaff(array $data): AdminUser
    {
        return AdminUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }

    protected function updateStaff(int $id, array $data): AdminUser
    {
        $staff = AdminUser::findOrFail($id);

        $staff->name = $data['name'];
        $staff->email = $data['email'];
        $staff->role = $data['role'];

        if (!empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }

        $staff->save();

        return $staff->fresh();
    }

    protected function deleteStaff(int $id): bool
    {
        $staff = AdminUser::findOrFail($id);
        return $staff->delete();
    }
}
