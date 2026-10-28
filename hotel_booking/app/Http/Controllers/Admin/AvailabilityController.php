<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    use ApiResponse;

    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Display availability calendar
     */
    public function calendar(Request $request)
    {
        return $this->availabilityService->calendar($request);
    }

    /**
     * Block dates
     */
    public function block(Request $request)
    {
        return $this->availabilityService->block($request);
    }

    /**
     * Unblock dates
     */
    public function unblock(Request $request, $id)
    {
        return $this->availabilityService->unblock($request, $id);
    }

    /**
     * List all active holds
     */
    public function listHolds(Request $request)
    {
        $result = $this->availabilityService->listActiveHolds();
        
        return $this->successResponse($result);
    }

    /**
     * Release a specific hold manually
     */
    public function releaseHold(Request $request, $id)
    {
        $result = $this->availabilityService->releaseHoldById($id);
        
        if ($result['success']) {
            return $this->successResponse(null, $result['message']);
        }
        
        return $this->errorResponse($result['message'], 400);
    }

    /**
     * Clean up all expired holds
     */
    public function cleanupExpiredHolds(Request $request)
    {
        $result = $this->availabilityService->cleanupAllExpiredHolds();
        
        return $this->successResponse(null, $result['message']);
    }
}

