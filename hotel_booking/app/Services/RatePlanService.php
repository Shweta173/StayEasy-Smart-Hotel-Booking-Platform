<?php

namespace App\Services;

use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\Property;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RatePlanService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $filters = [
            'room_type_id' => $request->input('room_type') ?: $request->input('room_type_id'),
            'property_id' => $request->input('property') ?: $request->input('property_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'per_page' => $request->input('per_page', 50),
        ];

        $ratePlans = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($ratePlans, 'Rate plans retrieved successfully');
        }

        // Return view for web requests
        $properties = Property::select('id', 'name')->orderBy('name')->get();
        $roomTypes = RoomType::with(['property' => function($query) {
            $query->select('properties.id', 'properties.name');
        }])->select('id', 'name', 'property_id')->orderBy('name')->get();
        return view('admin.rate-plans.index', compact('ratePlans', 'properties', 'roomTypes'));
    }
   
    public function create(Request $request)
    {
        $properties = Property::with('roomTypes')->orderBy('name')->get();
        
        // Get all room types with property relationship
        $allRoomTypes = RoomType::with('property')
            ->orderBy('name')
            ->get();
        
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.rate-plans.form', compact('properties', 'allRoomTypes'));
        }

        // Otherwise return full page
        return view('admin.rate-plans.create', compact('properties', 'allRoomTypes'));
    }
  
    public function store(Request $request)
    {
        try {
            $ratePlan = $this->createRatePlan($request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->createdResponse($ratePlan, 'Rate plan created successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.rate-plans.index')
                ->with('success', 'Rate plan created successfully');

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
   
    public function show(Request $request, $id)
    {
        $ratePlan = RatePlan::with(['roomType.property'])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($ratePlan, 'Rate plan retrieved successfully');
        }

        // Return view for web requests
        return view('admin.rate-plans.show', compact('ratePlan'));
    }
 
    public function edit(Request $request, $id)
    {
        $ratePlan = RatePlan::with(['roomType.property'])->findOrFail($id);
        $properties = Property::with('roomTypes')->orderBy('name')->get();
        
        // Get all room types with property relationship
        $allRoomTypes = RoomType::with('property')
            ->orderBy('name')
            ->get();
        
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.rate-plans.form', compact('ratePlan', 'properties', 'allRoomTypes'));
        }

        // Otherwise return full page
        return view('admin.rate-plans.edit', compact('ratePlan', 'properties', 'allRoomTypes'));
    }
 
    public function update(Request $request, $id)
    {
        try {
            $ratePlan = RatePlan::findOrFail($id);
            $ratePlan = $this->updateRatePlan($ratePlan, $request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($ratePlan, 'Rate plan updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.rate-plans.index')
                ->with('success', 'Rate plan updated successfully');

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
            $ratePlan = RatePlan::findOrFail($id);
            $this->deleteRatePlan($ratePlan);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->deletedResponse('Rate plan deleted successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.rate-plans.index')
                ->with('success', 'Rate plan deleted successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    public function calendar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'room_type_id' => 'required|exists:room_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $roomType = RoomType::findOrFail($request->room_type_id);
            
            $ratePlans = RatePlan::where('room_type_id', $request->room_type_id)
                ->where('end_date', '>=', $request->start_date)
                ->where('start_date', '<=', $request->end_date)
                ->orderBy('start_date')
                ->get();

            // Generate daily pricing
            $calendar = $this->generateCalendarPricing($roomType, $request->start_date, $request->end_date);

            $data = [
                'room_type' => $roomType,
                'base_price' => number_format($roomType->base_price, 2),
                'rate_plans' => $ratePlans,
                'calendar' => $calendar,
            ];

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($data, 'Calendar pricing retrieved successfully');
            }

            // Return view for web requests
            return view('admin.rate-plans.calendar', $data);

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }
    
    protected function generateCalendarPricing(RoomType $roomType, string $startDate, string $endDate): array
    {
        $calendar = [];
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        while ($currentDate->lte($endDate)) {
            $price = $roomType->getPriceForDate($currentDate);
            
            $calendar[] = [
                'date' => $currentDate->format('Y-m-d'),
                'price' => number_format($price, 2),
                'day_name' => $currentDate->format('l'),
            ];

            $currentDate->addDay();
        }

        return $calendar;
    }
   
    protected function getRules(?int $excludeId = null): array
    {
        $startDateRule = $excludeId ? 'required|date' : 'required|date|after_or_equal:today';
        
        return [
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'nullable|string|max:255',
            'price_per_night' => 'required|numeric|min:0|max:999999.99',
            'start_date' => $startDateRule,
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1|gte:min_nights',
        ];
    }
 
    public function createRatePlan(array $data): RatePlan
    {
        $validator = Validator::make($data, $this->getRules());
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($validator) {
            $validated = $validator->validated();
            
            // Get the room type to access base_price
            $roomType = RoomType::find($validated['room_type_id']);
            
            // Validate that price_per_night is >= base_price
            if ($validated['price_per_night'] < $roomType->base_price) {
                $validationValidator = Validator::make([], []);
                $validationValidator->errors()->add(
                    'price_per_night', 
                    "Price per night must be greater than or equal to the base price of $" . number_format($roomType->base_price, 2)
                );
                throw new ValidationException($validationValidator);
            }
            
            // Auto-generate name if not provided
            if (empty($validated['name'])) {
                $startDate = Carbon::parse($validated['start_date'])->format('M d, Y');
                $endDate = Carbon::parse($validated['end_date'])->format('M d, Y');
                $validated['name'] = "{$roomType->name} - {$startDate} to {$endDate}";
            }
            
            // Validate date range doesn't overlap with existing rate plans
            $this->validateDateRange(
                $validated['room_type_id'],
                $validated['start_date'],
                $validated['end_date']
            );

            $ratePlan = RatePlan::create($validated);
            return $ratePlan->load('roomType');
        });
    }
  
    public function updateRatePlan(RatePlan $ratePlan, array $data): RatePlan
    {
        $rules = $this->getRules($ratePlan->id);
        
        // Make rules optional for update
        foreach ($rules as $key => $rule) {
            if (is_string($rule) && str_contains($rule, 'required')) {
                $rules[$key] = str_replace('required|', 'sometimes|required|', $rule);
            }
        }
        
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($ratePlan, $validator) {
            $validated = $validator->validated();
            
            // Get the room type (either the new one or existing one)
            $roomTypeId = $validated['room_type_id'] ?? $ratePlan->room_type_id;
            $roomType = RoomType::find($roomTypeId);
            
            // Validate that price_per_night is >= base_price (if price is being updated)
            if (isset($validated['price_per_night']) && $validated['price_per_night'] < $roomType->base_price) {
                $validationValidator = Validator::make([], []);
                $validationValidator->errors()->add(
                    'price_per_night', 
                    "Price per night must be greater than or equal to the base price of $" . number_format($roomType->base_price, 2)
                );
                throw new ValidationException($validationValidator);
            }
            
            // Validate date range if dates are being changed
            if (isset($validated['start_date']) || isset($validated['end_date'])) {
                $this->validateDateRange(
                    $validated['room_type_id'] ?? $ratePlan->room_type_id,
                    $validated['start_date'] ?? $ratePlan->start_date,
                    $validated['end_date'] ?? $ratePlan->end_date,
                    $ratePlan->id // Exclude current rate plan from overlap check
                );
            }

            $ratePlan->update($validated);
            return $ratePlan->fresh()->load('roomType');
        });
    }
  
    public function deleteRatePlan(RatePlan $ratePlan): bool
    {
        return DB::transaction(function () use ($ratePlan) {
            return $ratePlan->delete();
        });
    }

    private function validateDateRange(
        int $roomTypeId,
        string $startDate,
        string $endDate,
        ?int $excludeRatePlanId = null
    ): void {
        $query = RatePlan::where('room_type_id', $roomTypeId)
            ->where(function ($q) use ($startDate, $endDate) {
                // Check for actual overlaps - allow touching dates
                // New plan overlaps if: new_start < existing_end AND new_end > existing_start
                $q->where('start_date', '<', $endDate)
                  ->where('end_date', '>', $startDate);
            });

        if ($excludeRatePlanId) {
            $query->where('id', '!=', $excludeRatePlanId);
        }

        if ($query->exists()) {
            // Get the conflicting rate plan for better error message
            $conflictingPlan = $query->first();
            $conflictStart = Carbon::parse($conflictingPlan->start_date)->format('M d, Y');
            $conflictEnd = Carbon::parse($conflictingPlan->end_date)->format('M d, Y');
            
            // Return validation error on both date fields
            $validator = Validator::make([], []);
            $validator->errors()->add('start_date', "Date range overlaps with existing rate plan ({$conflictStart} to {$conflictEnd})");
            $validator->errors()->add('end_date', "Date range overlaps with existing rate plan ({$conflictStart} to {$conflictEnd})");
            throw new ValidationException($validator);
        }
    }
  
    public function getCalendarPricing(int $roomTypeId, string $startDate, string $endDate): array
    {
        $ratePlans = RatePlan::where('room_type_id', $roomTypeId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            })
            ->get();

        $calendar = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start <= $end) {
            $dateStr = $start->format('Y-m-d');
            
            // Find applicable rate plan for this date
            $applicableRate = null;
            foreach ($ratePlans as $plan) {
                $planStart = Carbon::parse($plan->start_date);
                $planEnd = Carbon::parse($plan->end_date);
                
                if ($start >= $planStart && $start <= $planEnd) {
                    $applicableRate = $plan->price_per_night;
                    break;
                }
            }

            $calendar[$dateStr] = $applicableRate;
            $start->addDay();
        }

        return $calendar;
    }
  
    public function list(array $filters = [])
    {
        $query = RatePlan::with('roomType.property');

        // Filter by room type
        if (!empty($filters['room_type_id'])) {
            $query->where('room_type_id', $filters['room_type_id']);
        }

        // Filter by property
        if (!empty($filters['property_id'])) {
            $query->whereHas('roomType', function ($q) use ($filters) {
                $q->where('property_id', $filters['property_id']);
            });
        }

        // Filter by status (active, upcoming, expired)
        if (!empty($filters['status'])) {
            $today = now()->format('Y-m-d');
            
            switch ($filters['status']) {
                case 'active':
                    $query->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today);
                    break;
                case 'upcoming':
                    $query->where('start_date', '>', $today);
                    break;
                case 'expired':
                    $query->where('end_date', '<', $today);
                    break;
            }
        }

        // Filter by date range
        if (!empty($filters['date'])) {
            $query->where('start_date', '<=', $filters['date'])
                  ->where('end_date', '>=', $filters['date']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'start_date';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // Always return paginated results
        $perPage = $filters['per_page'] ?? 50;
        return $query->paginate($perPage);
    }
}

