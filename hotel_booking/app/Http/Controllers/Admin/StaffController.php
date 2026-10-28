<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * Display staff listing
     */
    public function index(Request $request)
    {
        return $this->staffService->index($request);
    }

    /**
     * Store new staff member
     */
    public function store(Request $request)
    {
        return $this->staffService->store($request);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, $id)
    {
        return $this->staffService->update($request, $id);
    }

    /**
     * Delete staff member
     */
    public function destroy(Request $request, $id)
    {
        return $this->staffService->destroy($request, $id);
    }
}
