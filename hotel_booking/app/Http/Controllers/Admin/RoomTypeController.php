<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoomTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    use ApiResponse;
    
    protected $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    /**
     * Display a listing of room types
     */
    public function index(Request $request)
    {
        return $this->roomTypeService->index($request);
    }

    /**
     * Show the form for creating a new room type
     */
    public function create(Request $request)
    {
        return $this->roomTypeService->create($request);
    }

    /**
     * Store a newly created room type
     */
    public function store(Request $request)
    {
        return $this->roomTypeService->store($request);
    }

    /**
     * Display the specified room type
     */
    public function show(Request $request, $id)
    {
        return $this->roomTypeService->show($request, $id);
    }

    /**
     * Show the form for editing the specified room type
     */
    public function edit(Request $request, $id)
    {
        return $this->roomTypeService->edit($request, $id);
    }

    /**
     * Update the specified room type
     */
    public function update(Request $request, $id)
    {
        return $this->roomTypeService->update($request, $id);
    }

    /**
     * Remove the specified room type
     */
    public function destroy(Request $request, $id)
    {
        return $this->roomTypeService->destroy($request, $id);
    }
}
