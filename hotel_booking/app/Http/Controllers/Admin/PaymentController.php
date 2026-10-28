<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display payments listing
     */
    public function index(Request $request)
    {
        return $this->paymentService->index($request);
    }

    /**
     * Process refund
     */
    public function refund(Request $request, $id)
    {
        return $this->paymentService->refund($request, $id);
    }
}
