<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;
    
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Get all reviews with filters
     */
    public function index(Request $request)
    {
        return $this->reviewService->index($request);
    }

    /**
     * Get single review details
     */
    public function show(Request $request, $id)
    {
        return $this->reviewService->show($request, $id);
    }

    /**
     * Approve a review
     */
    public function approve(Request $request, $id)
    {
        return $this->reviewService->approve($request, $id);
    }

    /**
     * Reject a review
     */
    public function reject(Request $request, $id)
    {
        return $this->reviewService->reject($request, $id);
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, $id)
    {
        return $this->reviewService->destroy($request, $id);
    }

    /**
     * Add staff reply to a review
     */
    public function addReply(Request $request, $id)
    {
        return $this->reviewService->addReply($request, $id);
    }
}
