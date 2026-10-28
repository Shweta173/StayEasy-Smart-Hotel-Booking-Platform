<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;
    
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of all orders
     */
    public function index(Request $request)
    {
        return $this->orderService->index($request);
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, $id)
    {
        return $this->orderService->show($request, $id);
    }

    /**
     * Get revenue statistics
     */
    public function statistics(Request $request)
    {
        return $this->orderService->statistics($request);
    }
}
