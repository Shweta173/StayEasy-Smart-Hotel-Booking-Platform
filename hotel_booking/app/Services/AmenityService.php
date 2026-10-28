<?php

namespace App\Services;

use App\Models\Amenity;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AmenityService
{
    use ApiResponse;

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 50),
        ];

        $amenities = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($amenities, 'Amenities retrieved successfully');
        }

        // Return view for web requests
        return view('admin.amenities.index', compact('amenities'));
    }

    
    public function store(Request $request)
    {
        try {
            $amenity = $this->createAmenity($request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->createdResponse($amenity, 'Amenity created successfully');
            }

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $amenity = $this->getById($id);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($amenity, 'Amenity retrieved successfully');
            }

            // Return view for web requests
            return view('admin.amenities.show', compact('amenity'));

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->notFoundResponse('Amenity not found');
            }
            return redirect()->route('admin.amenities.index')->with('error', 'Amenity not found');
        }
    }

    
    public function update(Request $request, $id)
    {
        try {
            $amenity = $this->updateAmenity($id, $request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($amenity, 'Amenity updated successfully');
            }

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->notFoundResponse('Amenity not found');
            }
            return redirect()->back()->with('error', 'Amenity not found');
        } catch (\Exception $e) {
            \Log::error('Amenity update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'id' => $id,
                'data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse('Operation failed while editing: ' . $e->getMessage());
            }
            return redirect()->back()->with('error', 'Operation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->deleteAmenity($id);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->deletedResponse('Amenity deleted successfully');
            }

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage(), null, 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    
    protected function getRules(?int $excludeId = null): array
    {
        $uniqueRule = $excludeId 
            ? "required|string|max:100|unique:amenities,name,{$excludeId}"
            : 'required|string|max:100|unique:amenities,name';
            
        return [
            'name' => $uniqueRule,
            'icon' => 'nullable|string|max:50',
        ];
    }

    
    public function list(array $filters = [])
    {
        $query = Amenity::withCount('roomTypes');

        // Search by name or icon
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('icon', 'like', "%{$search}%");
            });
        }

        // Always return paginated results
        $perPage = $filters['per_page'] ?? 50;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

   
    public function getById(int $id): Amenity
    {
        return Amenity::withCount('roomTypes')->findOrFail($id);
    }

    
    public function createAmenity(array $data): Amenity
    {
        $validator = Validator::make($data, $this->getRules());
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Amenity::create($validator->validated());
    }

    
    public function updateAmenity(int $id, array $data): Amenity
    {
        $amenity = Amenity::findOrFail($id);
        
        $validator = Validator::make($data, $this->getRules($id));
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $amenity->update($validator->validated());
        
        return $amenity->fresh();
    }

   
    public function deleteAmenity(int $id): bool
    {
        $amenity = Amenity::findOrFail($id);
        
        // Check if amenity is being used by any room types
        $roomTypesCount = $amenity->roomTypes()->count();
        
        if ($roomTypesCount > 0) {
            throw new \Exception("Cannot delete amenity. It is being used by {$roomTypesCount} room type(s).");
        }

        return $amenity->delete();
    }
}
