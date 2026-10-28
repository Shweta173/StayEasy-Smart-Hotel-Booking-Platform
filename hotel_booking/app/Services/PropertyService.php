<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PropertyService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'city' => $request->input('city'),
            'country' => $request->input('country'),
            'is_active' => $request->input('is_active'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'per_page' => $request->input('per_page', 15),
        ];

        $properties = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($properties, 'Properties retrieved successfully');
        }

        // Return view for web requests
        return view('admin.properties.index', compact('properties'));
    }

    
    public function create(Request $request)
    {
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.properties.form');
        }

        // Otherwise return full page
        return view('admin.properties.create');
    }
  
    public function store(Request $request)
    {
        try {
            $property = $this->createProperty(
                $request->all(),
                $request->file('images', [])
            );

            // Return JSON for API requests or AJAX
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->createdResponse($property, 'Property created successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.properties')
                ->with('success', 'Property created successfully');
                
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
   
    public function show(Request $request, $id)
    {
        $property = Property::with([
            'images', 
            'roomTypes.amenities', 
            'roomTypes.ratePlans',
            'reviews.guest'
        ])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($property, 'Property retrieved successfully');
        }

        // Return view for web requests
        return view('admin.properties.show', compact('property'));
    }

    public function edit(Request $request, $id)
    {
        $property = Property::with('images')->findOrFail($id);
        
        // If AJAX request (from slide panel), return only the form
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.properties.form', compact('property'));
        }

        // Otherwise return full page
        return view('admin.properties.edit', compact('property'));
    }
 
    public function update(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            
            $property = $this->updateProperty(
                $property,
                $request->all(),
                $request->file('images', [])
            );

            // Return JSON for API requests or AJAX
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($property, 'Property updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.properties')
                ->with('success', 'Property updated successfully');
                
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
   
    public function destroy(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            $this->deleteProperty($property);

            // Return JSON for API requests or AJAX
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->deletedResponse('Property deleted successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.properties')
                ->with('success', 'Property deleted successfully');
                
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse($e->getMessage());
            }
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
   
    public function toggleStatus(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            
            // Toggle between 'draft' and 'published'
            $property->status = ($property->status === 'published') ? 'draft' : 'published';
            $property->save();

            // Detect if it's an AJAX/JSON request
            if ($request->expectsJson() || $request->ajax()) {
                return $this->successResponse(
                    ['status' => $property->status],
                    'Property status updated successfully'
                );
            }

            // For web requests, redirect back
            return redirect()->back()->with('success', 'Property status updated successfully');
            
        } catch (\Exception $e) {
            \Log::error('Toggle status error: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return $this->errorResponse('Failed to update status: ' . $e->getMessage());
            }
            
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }
   
    public function setPrimaryImage(Request $request, $imageId)
    {
        try {
            $image = PropertyImage::findOrFail($imageId);
            $this->setPrimaryImageLogic($image);

            return $this->successResponse(null, 'Primary image set successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to set primary image');
        }
    }
   
    public function deleteImage(Request $request, $id)
    {
        try {
            $image = PropertyImage::findOrFail($id);
            $this->deleteImageLogic($image);

            return $this->deletedResponse('Image deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete image');
        }
    }
   
    public function uploadImages(Request $request, $propertyId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:10',
                'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $property = Property::findOrFail($propertyId);
            $uploadedImages = $this->handleMultipleImageUploads($property, $request->file('images'));

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->createdResponse($uploadedImages, count($uploadedImages) . ' image(s) uploaded successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.properties.edit', $propertyId)
                ->with('success', count($uploadedImages) . ' image(s) uploaded successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
   
    public function updateImageOrder(Request $request, $propertyId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array',
                'images.*.id' => 'required|exists:property_images,id',
                'images.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $property = Property::findOrFail($propertyId);
            $this->updateImageOrderLogic($property, $request->images);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse(null, 'Image order updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.properties.edit', $propertyId)
                ->with('success', 'Image order updated successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
  
    protected function getCreateRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z0-9\s\-.:]+$/', 'unique:properties,name'],
            'description' => 'required|string|max:5000',
            'address' => 'required|string|max:500',
            'city' => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'state' => ['nullable', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'postal_code' => 'nullable|string|max:20',
            'country' => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ];
    }
   
    protected function getUpdateRules(?int $propertyId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z0-9\s\-.:]+$/', 'unique:properties,name,' . $propertyId],
            'description' => 'required|string|max:5000',
            'address' => 'required|string|max:500',
            'city' => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'state' => ['nullable', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'postal_code' => 'nullable|string|max:20',
            'country' => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z\s]+$/'],
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ];
        
        // Make all fields optional for updates
        foreach ($rules as $key => $rule) {
            if (is_string($rule) && !str_contains($rule, 'sometimes')) {
                $rules[$key] = str_replace('required|', 'sometimes|required|', $rule);
            } elseif (is_array($rule) && !in_array('sometimes', $rule)) {
                array_unshift($rules[$key], 'sometimes', 'required');
            }
        }
        
        return $rules;
    }
 
    protected function getImageRules(): array
    {
        return [
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB
        ];
    }
  
    protected function createProperty(array $data, array $images = []): Property
    {
        // Validate property data
        $validator = Validator::make($data, $this->getCreateRules());
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate images if provided
        if (!empty($images)) {
            $imageValidator = Validator::make(['images' => $images], $this->getImageRules());
            if ($imageValidator->fails()) {
                throw new ValidationException($imageValidator);
            }
        }

        return DB::transaction(function () use ($validator, $images) {
            // Prepare property data with created_by from session
            $propertyData = $validator->validated();
            $propertyData['created_by'] = session('admin_id');
            
            // Set default status if not provided
            if (!isset($propertyData['status'])) {
                $propertyData['status'] = 'draft';
            }

            // Create property
            $property = Property::create($propertyData);

            // Handle image uploads
            if (!empty($images)) {
                $this->uploadImagesLogic($property, $images);
            }

            return $property->load('images');
        });
    }
    
    protected function updateProperty(Property $property, array $data, array $images = []): Property
    {
        // Validate property data with property ID to exclude current property from unique check
        $validator = Validator::make($data, $this->getUpdateRules($property->id));
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate images if provided
        if (!empty($images)) {
            $imageValidator = Validator::make(['images' => $images], $this->getImageRules());
            if ($imageValidator->fails()) {
                throw new ValidationException($imageValidator);
            }
        }

        return DB::transaction(function () use ($property, $validator, $images) {
            // Update property data
            $property->update($validator->validated());

            // Handle new image uploads
            if (!empty($images)) {
                $this->uploadImagesLogic($property, $images);
            }

            return $property->fresh()->load('images');
        });
    }
   
    protected function deleteProperty(Property $property): bool
    {
        return DB::transaction(function () use ($property) {
            // Delete all property images from storage
            foreach ($property->images as $image) {
                $this->deleteImageLogic($image);
            }

            // Delete the property
            return $property->delete();
        });
    }
   
    private function uploadImagesLogic(Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $this->uploadImage($property, $image, $index);
            }
        }
    }
   
    private function uploadImage(Property $property, UploadedFile $image, int $order = 0): PropertyImage
    {
        // Validate the uploaded file
        if (!$image->isValid()) {
            throw new \Exception('Invalid image file uploaded');
        }

        // Store the image
        $path = $image->store('property-images', 'public');
        
        if (!$path) {
            throw new \Exception('Failed to store image file');
        }

        // Verify the file was actually saved
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('Image file was not saved to storage');
        }

        // Create database record
        $propertyImage = PropertyImage::create([
            'property_id' => $property->id,
            'image_url' => '/storage/' . $path,
            'is_primary' => $property->images()->count() === 0,
            'sort_order' => $order,
        ]);

        \Log::info('Image uploaded successfully', [
            'property_id' => $property->id,
            'image_url' => '/storage/' . $path,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ]);

        return $propertyImage;
    }
   
    private function setPrimaryImageLogic(PropertyImage $image): PropertyImage
    {
        return DB::transaction(function () use ($image) {
            PropertyImage::where('property_id', $image->property_id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return $image->fresh();
        });
    }
   
    private function updateImageOrderLogic(Property $property, array $images): void
    {
        DB::transaction(function () use ($property, $images) {
            foreach ($images as $imageData) {
                PropertyImage::where('id', $imageData['id'])
                    ->where('property_id', $property->id)
                    ->update(['sort_order' => $imageData['sort_order']]);
            }
        });
    }
  
    private function handleMultipleImageUploads(Property $property, array $images): array
    {
        $uploadedImages = [];
        $maxSortOrder = PropertyImage::where('property_id', $property->id)->max('sort_order') ?? 0;

        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                // Validate the uploaded file
                if (!$image->isValid()) {
                    \Log::warning('Invalid image file skipped', ['index' => $index]);
                    continue;
                }

                // Store the image
                $path = $image->store('property-images', 'public');
                
                if (!$path) {
                    \Log::error('Failed to store image file', ['index' => $index]);
                    continue;
                }

                // Verify the file was actually saved
                if (!Storage::disk('public')->exists($path)) {
                    \Log::error('Image file was not saved to storage', ['path' => $path]);
                    continue;
                }

                $propertyImage = PropertyImage::create([
                    'property_id' => $property->id,
                    'image_url' => '/storage/' . $path,
                    'sort_order' => $maxSortOrder + $index + 1,
                    'is_primary' => $property->images()->count() === 0 && $index === 0,
                ]);

                \Log::info('Image uploaded successfully', [
                    'property_id' => $property->id,
                    'image_url' => '/storage/' . $path,
                    'file_size' => $image->getSize(),
                ]);

                $uploadedImages[] = $propertyImage;
            }
        }

        return $uploadedImages;
    }
   
    private function deleteImageLogic(PropertyImage $image): bool
    {
        return DB::transaction(function () use ($image) {
            // Extract path from image_url (remove /storage/ prefix)
            $path = str_replace('/storage/', '', $image->image_url);
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return $image->delete();
        });
    }
   
    protected function list(array $filters = [])
    {
        $query = Property::with(['images', 'roomTypes']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Filter by status (published/draft)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
}
