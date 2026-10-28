<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    use ApiResponse;
    
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * Display a listing of properties
     */
    public function index(Request $request)
    {
        return $this->propertyService->index($request);
    }

    /**
     * Show the form for creating a new property
     */
    public function create(Request $request)
    {
        return $this->propertyService->create($request);
    }

    /**
     * Store a newly created property
     */
    public function store(Request $request)
    {
        return $this->propertyService->store($request);
    }

    /**
     * Display the specified property
     */
    public function show(Request $request, $id)
    {
        return $this->propertyService->show($request, $id);
    }

    /**
     * Show the form for editing the specified property
     */
    public function edit(Request $request, $id)
    {
        return $this->propertyService->edit($request, $id);
    }

    /**
     * Update the specified property
     */
    public function update(Request $request, $id)
    {
        return $this->propertyService->update($request, $id);
    }

    /**
     * Remove the specified property
     */
    public function destroy(Request $request, $id)
    {
        return $this->propertyService->destroy($request, $id);
    }

    /**
     * Toggle property active status
     */
    public function toggleStatus(Request $request, $id)
    {
        return $this->propertyService->toggleStatus($request, $id);
    }

    /**
     * Set property image as primary
     */
    public function setPrimaryImage(Request $request, $imageId)
    {
        return $this->propertyService->setPrimaryImage($request, $imageId);
    }

    /**
     * Delete property image
     */
    public function deleteImage(Request $request, $id)
    {
        return $this->propertyService->deleteImage($request, $id);
    }

    /**
     * Upload images for a property
     */
    public function uploadImages(Request $request, $propertyId)
    {
        return $this->propertyService->uploadImages($request, $propertyId);
    }

    /**
     * Update image sort order
     */
    public function updateImageOrder(Request $request, $propertyId)
    {
        return $this->propertyService->updateImageOrder($request, $propertyId);
    }
}
