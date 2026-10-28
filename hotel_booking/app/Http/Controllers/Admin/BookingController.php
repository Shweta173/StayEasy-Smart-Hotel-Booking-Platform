<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;
    
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display a listing of all bookings
     */
    public function index(Request $request)
    {
        return $this->bookingService->index($request);
    }

    /**
     * Display the specified booking
     */
    public function show(Request $request, $id)
    {
        return $this->bookingService->show($request, $id);
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        return $this->bookingService->updateStatus($request, $id);
    }

    /**
     * Get booking statistics
     */
    public function statistics(Request $request)
    {
        return $this->bookingService->statistics($request);
    }

    /**
     * Get today's arrivals and departures
     */
    public function todayActivity(Request $request)
    {
        return $this->bookingService->todayActivity($request);
    }

    /**
     * Check in a guest
     */
    public function checkIn(Request $request, $id)
    {
        return $this->bookingService->handleCheckIn($request, $id);
    }

    /**
     * Check out a guest
     */
    public function checkOut(Request $request, $id)
    {
        return $this->bookingService->handleCheckOut($request, $id);
    }
}
