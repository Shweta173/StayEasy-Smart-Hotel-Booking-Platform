<?php

namespace App\Services;

use App\Models\RoomType;
use App\Models\Property;
use App\Models\Amenity;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class RoomTypeService
{
    use ApiResponse;

    public function index(Request $request)
    {
        $filters = [
            'property_id' => $request->input('property') ?: $request->input('property_id'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 50),
        ];

        $roomTypes = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($roomTypes, 'Room types retrieved successfully');
        }

        // Return view for web requests
        $properties = Property::select('id', 'name')->orderBy('name')->get();
        return view('admin.room-types.index', compact('roomTypes', 'properties'));
    }

    public function create(Request $request)
    {
        $properties = Property::select('id', 'name')->orderBy('name')->get();
        $amenities = Amenity::select('id', 'name')->orderBy('name')->get();
        
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.room-types.form', compact('properties', 'amenities'));
        }

        // Otherwise return full page
        return view('admin.room-types.create', compact('properties', 'amenities'));
    }

    public function store(Request $request)
    {
        try {
            $roomType = $this->createRoomType($request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->createdResponse($roomType, 'Room type created successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.room-types.index')
                ->with('success', 'Room type created successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Room type creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse('Operation failed while adding the amenities: ' . $e->getMessage());
            }
            return redirect()->back()->with('error', 'Operation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $roomType = RoomType::with([
            'property',
            'amenities',
            'ratePlans',
            'bookings' => function ($query) {
                $query->orderBy('check_in', 'desc')->limit(20);
            }
        ])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($roomType, 'Room type retrieved successfully');
        }

        // Return view for web requests
        return view('admin.room-types.show', compact('roomType'));
    }

    public function edit(Request $request, $id)
    {
        $roomType = RoomType::with(['property', 'amenities'])->findOrFail($id);
        $properties = Property::select('id', 'name')->orderBy('name')->get();
        $amenities = Amenity::select('id', 'name')->orderBy('name')->get();
        
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.room-types.form', compact('roomType', 'properties', 'amenities'));
        }

        // Otherwise return full page
        return view('admin.room-types.edit', compact('roomType', 'properties', 'amenities'));
    }

    public function update(Request $request, $id)
    {
        try {
            $roomType = RoomType::findOrFail($id);
            $roomType = $this->updateRoomType($roomType, $request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($roomType, 'Room type updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.room-types.index')
                ->with('success', 'Room type updated successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $roomType = RoomType::findOrFail($id);
            $this->deleteRoomType($roomType);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->deletedResponse('Room type deleted successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.room-types.index')
                ->with('success', 'Room type deleted successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function getRules(?int $id = null, ?int $propertyId = null): array
    {
        // Build unique rule for name - unique within the same property
        $nameRules = ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z0-9\s\-.:]+$/'];
        
        // Add unique validation using Rule class for better clarity
        if ($propertyId) {
            // When property is specified, ensure name is unique within that property
            $uniqueRule = Rule::unique('room_types', 'name')
                ->where('property_id', $propertyId);
            
            if ($id) {
                $uniqueRule->ignore($id);
            }
            
            $nameRules[] = $uniqueRule;
        } else {
            // Fallback: just check if name is unique across all room types
            if ($id) {
                $nameRules[] = 'unique:room_types,name,' . $id;
            } else {
                $nameRules[] = 'unique:room_types,name';
            }
        }
        
        return [
            'property_id' => 'required|exists:properties,id',
            'name' => $nameRules,
            'description' => 'nullable|string|max:2000',
            'base_price' => 'required|numeric|min:0.01|max:999999.99',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'total_units' => 'required|integer|min:1|max:1000',
            'size_sqm' => 'nullable|numeric|min:0',
            'bed_type' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }

    public function createRoomType(array $data): RoomType
    {
        $propertyId = $data['property_id'] ?? null;
        $validator = Validator::make($data, $this->getRules(null, $propertyId));
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($validator) {
            $validated = $validator->validated();
            $amenities = $validated['amenities'] ?? [];
            unset($validated['amenities']);
            
            $roomType = RoomType::create($validated);
            
            // Sync amenities if provided
            if (!empty($amenities)) {
                $roomType->amenities()->sync($amenities);
            }
            
            return $roomType->load(['property', 'ratePlans', 'amenities']);
        });
    }

    public function updateRoomType(RoomType $roomType, array $data): RoomType
    {
        // Make rules optional for update
        $rules = $this->getRules($roomType->id);
        foreach ($rules as $key => $rule) {
            if (is_string($rule) && str_contains($rule, 'required')) {
                $rules[$key] = str_replace('required|', 'sometimes|required|', $rule);
            }
        }
        
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($roomType, $validator) {
            $validated = $validator->validated();
            $amenities = $validated['amenities'] ?? null;
            unset($validated['amenities']);
            
            $roomType->update($validated);
            
            // Sync amenities if provided
            if ($amenities !== null) {
                $roomType->amenities()->sync($amenities);
            }
            
            return $roomType->fresh()->load(['property', 'ratePlans', 'amenities']);
        });
    }

    public function deleteRoomType(RoomType $roomType): bool
    {
        return DB::transaction(function () use ($roomType) {
            // Check if there are any bookings
            if ($roomType->bookings()->exists()) {
                throw new \Exception('Cannot delete room type with existing bookings');
            }

            return $roomType->delete();
        });
    }

    public function list(array $filters = [])
    {
        $query = RoomType::with(['property', 'ratePlans', 'amenities']);

        // Filter by property
        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        // Search by name
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Filter by capacity
        if (!empty($filters['max_occupancy'])) {
            $query->where('max_occupancy', '>=', $filters['max_occupancy']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // Always return paginated results
        $perPage = $filters['per_page'] ?? 50;
        return $query->paginate($perPage);
    }
}
