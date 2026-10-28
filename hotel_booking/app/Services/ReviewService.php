<?php

namespace App\Services;

use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    use ApiResponse;
   
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'rating' => $request->input('rating'),
            'has_reply' => $request->input('has_reply'),
            'property_id' => $request->input('property_id'),
            'search' => $request->input('search'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'per_page' => $request->input('per_page', 15),
        ];

        $reviews = $this->list($filters);

        // Get statistics
        $stats = $this->getStatistics();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'reviews' => $reviews,
                'statistics' => $stats,
            ], 'Reviews retrieved successfully');
        }

        // Return view for web requests
        return view('admin.reviews', compact('reviews', 'stats'));
    }
   
    public function show(Request $request, $id)
    {
        $review = Review::with(['property', 'booking.guest'])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($review, 'Review retrieved successfully');
        }

        // For AJAX web requests, determine which view to return based on route name
        $routeName = $request->route()->getName();
        
        if ($routeName === 'admin.reviews.reply-form') {
            return view('admin.reviews.reply-form', compact('review'));
        }
        
        if ($routeName === 'admin.reviews.view') {
            return view('admin.reviews.view', compact('review'));
        }

        // Default: return JSON
        return response()->json([
            'msgtype' => 'success',
            'data' => $review
        ]);
    }
   
    public function approve(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);
            $review = $this->approveReview($review);

            // Always return JSON for AJAX/API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($review, 'Review approved successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.reviews')
                ->with('success', 'Review approved successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
   
    public function reject(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $review = Review::findOrFail($id);
            $review = $this->rejectReview($review, $request->get('reason'));

            // Always return JSON for AJAX/API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($review, 'Review rejected');
            }

            // Return redirect for web requests
            return redirect()->route('admin.reviews')
                ->with('success', 'Review rejected');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors());
        }
    }
   
    public function destroy(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();

            // Always return JSON for AJAX/API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->deletedResponse('Review deleted successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.reviews')
                ->with('success', 'Review deleted successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
  
    public function addReply(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reply' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $review = Review::findOrFail($id);
            $review = $this->addStaffReply($review, $request->reply);

            // Always return JSON for AJAX/API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->successResponse($review, 'Reply added successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.reviews')
                ->with('success', 'Reply added successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors());
        }
    }
   
    protected function list(array $filters = [])
    {
        $query = Review::with(['property', 'booking.guest']);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by rating
        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        // Filter by has_reply (whether staff_reply is not null)
        if (!empty($filters['has_reply'])) {
            if ($filters['has_reply'] === 'yes') {
                $query->whereNotNull('staff_reply');
            } elseif ($filters['has_reply'] === 'no') {
                $query->whereNull('staff_reply');
            }
        }

        // Filter by property
        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        // Search by guest name, property name, or booking reference
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('booking', function($bq) use ($search) {
                      $bq->where('booking_reference', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
 
    protected function getStatistics(): array
    {
        $avgRating = Review::where('status', 'approved')->avg('rating') ?? 0;
        
        return [
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'total' => Review::count(),
            'avg_rating' => round($avgRating, 1),
        ];
    }
 
    protected function approveReview(Review $review): Review
    {
        $review->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->guard('admin')->id()
        ]);

        return $review->fresh();
    }
  
    protected function rejectReview(Review $review, ?string $reason = null): Review
    {
        $review->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now(),
            'rejected_by' => auth()->guard('admin')->id()
        ]);

        return $review->fresh();
    }
   
    protected function addStaffReply(Review $review, string $reply): Review
    {
        $review->update([
            'staff_reply' => $reply,
            'replied_at' => now(),
            'replied_by' => auth()->guard('admin')->id()
        ]);

        return $review->fresh();
    }
}
