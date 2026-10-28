<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AmenityService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    use ApiResponse;
    
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    /**
     * Display a listing of amenities
     */
    public function index(Request $request)
    {
        return $this->amenityService->index($request);
    }

    /**
     * Store a newly created amenity
     */
    public function store(Request $request)
    {
        return $this->amenityService->store($request);
    }

    /**
     * Display the specified amenity
     */
    public function show(Request $request, $id)
    {
        return $this->amenityService->show($request, $id);
    }

    /**
     * Update the specified amenity
     */
    public function update(Request $request, $id)
    {
        return $this->amenityService->update($request, $id);
    }

    /**
     * Remove the specified amenity
     */
    public function destroy(Request $request, $id)
    {
        return $this->amenityService->destroy($request, $id);
    }
}
