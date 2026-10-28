<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GuestService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    protected $guestService;

    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    /**
     * Display listing of guests
     */
    public function index(Request $request)
    {
        return $this->guestService->index($request);
    }

    /**
     * Display guest details
     */
    public function show(Request $request, $id)
    {
        return $this->guestService->show($request, $id);
    }

    /**
     * Get guest bookings
     */
    public function bookings(Request $request, $id)
    {
        return $this->guestService->bookings($request, $id);
    }
}
