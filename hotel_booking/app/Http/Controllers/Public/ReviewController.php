<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Submit a new review
     * Status will be 'pending' until admin approves
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'guest_id' => 'nullable|exists:guests,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reviewData = [
                'property_id' => $request->property_id,
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending', // Requires admin approval
            ];

            // If guest_id is provided (authenticated user), include it
            if ($request->has('guest_id') && $request->guest_id) {
                $reviewData['guest_id'] = $request->guest_id;
            }

            $review = Review::create($reviewData);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after admin approval.',
                'review' => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approved reviews for a property
     */
    public function getPropertyReviews($propertyId)
    {
        $property = Property::findOrFail($propertyId);
        
        $reviews = Review::where('property_id', $propertyId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');
        
        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => $averageRating ? round($averageRating, 1) : 0,
            'total_reviews' => $reviews->count(),
            'property_name' => $property->name
        ]);
    }

    /**
     * Get all approved reviews (for reviews page)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        
        $reviews = Review::with('property')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'reviews' => $reviews
        ]);
    }
}
