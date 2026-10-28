<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RatePlanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RatePlanController extends Controller
{
    use ApiResponse;
    
    protected $ratePlanService;

    public function __construct(RatePlanService $ratePlanService)
    {
        $this->ratePlanService = $ratePlanService;
    }

    /**
     * Display a listing of rate plans
     */
    public function index(Request $request)
    {
        return $this->ratePlanService->index($request);
    }

    /**
     * Show the form for creating a new rate plan
     */
    public function create(Request $request)
    {
        return $this->ratePlanService->create($request);
    }

    /**
     * Store a newly created rate plan
     */
    public function store(Request $request)
    {
        return $this->ratePlanService->store($request);
    }

    /**
     * Display the specified rate plan
     */
    public function show(Request $request, $id)
    {
        return $this->ratePlanService->show($request, $id);
    }

    /**
     * Show the form for editing the specified rate plan
     */
    public function edit(Request $request, $id)
    {
        return $this->ratePlanService->edit($request, $id);
    }

    /**
     * Update the specified rate plan
     */
    public function update(Request $request, $id)
    {
        return $this->ratePlanService->update($request, $id);
    }

    /**
     * Remove the specified rate plan
     */
    public function destroy(Request $request, $id)
    {
        return $this->ratePlanService->destroy($request, $id);
    }

    /**
     * Get pricing calendar for a room type
     */
    public function calendar(Request $request)
    {
        return $this->ratePlanService->calendar($request);
    }
}
