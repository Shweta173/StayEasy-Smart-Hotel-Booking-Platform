<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\AvailabilityBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * A room type is "on sale" for a given date window if it has at least one
     * rate plan that covers the full window:
     *   plan.start_date <= checkIn  AND  plan.end_date >= checkOut
     *
     * Rooms with NO rate plans at all are treated as always visible (base price
     * applies — this lets properties show up before rates are fully configured
     * only if you choose that behaviour; set $requireRatePlan = true to hide them).
     */
    private function roomTypeHasValidRatePlan(RoomType $room, Carbon $checkIn, Carbon $checkOut): bool
    {
        $plans = $room->ratePlans;

        // No rate plans → hide from guests (must configure dates first)
        if ($plans->isEmpty()) {
            return false;
        }

        // Check if dates have valid rate plan
        $hasValidRatePlan = $plans->contains(function ($plan) use ($checkIn, $checkOut) {
            return Carbon::parse($plan->start_date)->lte($checkIn)
                && Carbon::parse($plan->end_date)->gte($checkOut);
        });

        if (!$hasValidRatePlan) {
            return false;
        }

        // Check if dates are blocked using AvailabilityBlock model
        // Only block if guest would CHECK IN during blocked period
        // Allow checkout on block start date (guest leaves at 11 AM, block starts at 2 PM)
        // block.start_date < checkOut AND block.end_date >= checkIn
        $hasBlockedDates = AvailabilityBlock::where('room_type_id', $room->id)
            ->where('start_date', '<', $checkOut->format('Y-m-d'))
            ->where('end_date', '>=', $checkIn->format('Y-m-d'))
            ->exists();

        // If dates are blocked, exclude this room type
        if ($hasBlockedDates) {
            return false;
        }

        return true;
    }

    /**
     * Frontend Page 1: Home / Landing
     * Featured properties — only show properties that have at least one room
     * with an active rate plan covering today. Show placeholder if no images.
     */
    public function featured()
    {
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $properties = Property::published()
            ->with(['primaryImage', 'images', 'roomTypes.ratePlans'])
            ->whereHas('roomTypes.ratePlans', function ($q) use ($today, $tomorrow) {
                $q->where('start_date', '<=', $today)
                  ->where('end_date',   '>=', $tomorrow);
            })
            ->take(6)
            ->get()
            ->map(function ($property) {
                // Get image: primary first, then any available image, only use placeholder if NO images exist
                $image = $property->primaryImage?->image_url 
                    ?? $property->images->first()?->image_url 
                    ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600';
                
                return [
                    'id'             => $property->id,
                    'name'           => $property->name,
                    'location'       => $property->location,
                    'city'           => $property->city,
                    'state'          => $property->state,
                    'country'        => $property->country,
                    'description'    => substr($property->description, 0, 150) . '...',
                    'image'          => $image,
                    'min_price'      => $property->min_price,
                    'average_rating' => $property->average_rating ?? 0,
                    'review_count'   => $property->reviews()->where('status', 'approved')->count(),
                ];
            });

        return response()->json([
            'success'    => true,
            'properties' => $properties,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Frontend Page 2: Search Results / Property Listing
     * Only show properties that have at least one room whose rate plan
     * covers the requested check_in → check_out window AND have at least one image.
     * If no dates are supplied, falls back to "has any rate plan active today".
     */
    public function search(Request $request)
    {
        // Determine the search window for rate-plan filtering
        $checkIn  = $request->has('check_in')  ? Carbon::parse($request->check_in)  : Carbon::today();
        $checkOut = $request->has('check_out') ? Carbon::parse($request->check_out) : Carbon::tomorrow();

        $query = Property::published()
            ->with(['primaryImage', 'images', 'roomTypes.ratePlans', 'reviews' => function($q) {
                $q->where('reviews.status', 'approved');
            }])
            // Only return properties that have at least one room on sale for the dates
            ->whereHas('roomTypes.ratePlans', function ($q) use ($checkIn, $checkOut) {
                $q->where('start_date', '<=', $checkIn->format('Y-m-d'))
                  ->where('end_date',   '>=', $checkOut->format('Y-m-d'));
            });

        // ── Location search ───────────────────────────────────────────────
        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $q->where('location', 'like', "%{$request->location}%")
                  ->orWhere('city',    'like', "%{$request->location}%")
                  ->orWhere('state',   'like', "%{$request->location}%")
                  ->orWhere('name',    'like', "%{$request->location}%")
                  ->orWhere('address', 'like', "%{$request->location}%");
            });
        }

        // ── Price range ───────────────────────────────────────────────────
        if ($request->filled('min_price') && $request->min_price > 0) {
            $query->whereHas('roomTypes', fn($q) =>
                $q->where('base_price', '>=', $request->min_price));
        }

        if ($request->filled('max_price') && $request->max_price > 0) {
            $query->whereHas('roomTypes', fn($q) =>
                $q->where('base_price', '<=', $request->max_price));
        }

        // ── Rating ────────────────────────────────────────────────────────
        // ── Rating ────────────────────────────────────────────────────────
        if ($request->filled('rating') && $request->rating > 0) {
            // Join reviews table for rating filter - use inner join to only show properties with reviews
            $query->join('reviews', function($join) {
                    $join->on('properties.id', '=', 'reviews.property_id')
                         ->where('reviews.status', '=', 'approved');
                  })
                  ->select('properties.*', DB::raw('AVG(reviews.rating) as avg_rating'))
                  ->groupBy('properties.id', 'properties.name', 'properties.description', 'properties.location',
                           'properties.city', 'properties.state', 'properties.country', 'properties.address',
                           'properties.postal_code', 'properties.status', 'properties.created_by',
                           'properties.created_at', 'properties.updated_at')
                  ->havingRaw('AVG(reviews.rating) >= ?', [$request->rating]);
        }

        // ── Amenities ─────────────────────────────────────────────────────
        if ($request->filled('amenities')) {
            $amenities = is_array($request->amenities)
                ? $request->amenities
                : array_filter(explode(',', $request->amenities));

            if (!empty($amenities)) {
                $query->whereHas('roomTypes.amenities', function ($q) use ($amenities) {
                    $q->whereIn('amenities.name', $amenities)
                      ->orWhereIn('amenities.id',  $amenities);
                });
            }
        }

        // ── Sorting ───────────────────────────────────────────────────────
        $sortBy    = $request->get('sort_by',    'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $hasRatingFilter = $request->filled('rating') && $request->rating > 0;

        if ($sortBy === 'price') {
            // If rating filter is applied, reviews are already joined
            if (!$hasRatingFilter) {
                $query->leftJoin('room_types', 'properties.id', '=', 'room_types.property_id')
                      ->select('properties.*', DB::raw('MIN(room_types.base_price) as min_room_price'))
                      ->groupBy('properties.id', 'properties.name', 'properties.description', 'properties.location',
                               'properties.city', 'properties.state', 'properties.country', 'properties.address',
                               'properties.postal_code', 'properties.status', 'properties.created_by',
                               'properties.created_at', 'properties.updated_at');
            } else {
                $query->leftJoin('room_types', 'properties.id', '=', 'room_types.property_id')
                      ->addSelect(DB::raw('MIN(room_types.base_price) as min_room_price'));
            }
            $query->orderBy('min_room_price', $sortOrder);
        } elseif ($sortBy === 'rating') {
            // If rating filter is applied, reviews are already joined
            if (!$hasRatingFilter) {
                $query->leftJoin('reviews', function($join) {
                        $join->on('properties.id', '=', 'reviews.property_id')
                             ->where('reviews.status', '=', 'approved');
                      })
                      ->select('properties.*', DB::raw('AVG(reviews.rating) as avg_rating'))
                      ->groupBy('properties.id', 'properties.name', 'properties.description', 'properties.location',
                               'properties.city', 'properties.state', 'properties.country', 'properties.address',
                               'properties.postal_code', 'properties.status', 'properties.created_by',
                               'properties.created_at', 'properties.updated_at');
            }
            $query->orderBy('avg_rating', $sortOrder);
        } else {
            // Default sorting by name or other fields
            $query->orderBy($sortBy, $sortOrder);
        }

        $properties = $query->paginate(12)->through(function ($property) use ($checkIn, $checkOut) {
            // Only include room types that have a rate plan covering the dates AND are not blocked
            $validRooms = $property->roomTypes->filter(function ($room) use ($checkIn, $checkOut) {
                return $this->roomTypeHasValidRatePlan($room, $checkIn, $checkOut);
            });

            // Skip properties with no valid rooms
            if ($validRooms->isEmpty()) {
                return null;
            }

            // Get image: primary first, then any available image, only use placeholder if NO images exist
            $image = $property->primaryImage?->image_url 
                ?? $property->images->first()?->image_url 
                ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600';
            
            return [
                'id'             => $property->id,
                'name'           => $property->name,
                'location'       => $property->location,
                'city'           => $property->city,
                'state'          => $property->state,
                'country'        => $property->country,
                'description'    => substr($property->description, 0, 200) . '...',
                'image'          => $image,
                'min_price'      => $property->min_price,
                'average_rating' => round($property->average_rating ?? 0, 1),
                'total_reviews'  => $property->reviews->count(),
                'room_types'     => $validRooms->map(function ($room) {
                    return [
                        'id'            => $room->id,
                        'name'          => $room->name,
                        'base_price'    => $room->base_price,
                        'max_occupancy' => $room->max_occupancy,
                    ];
                })->values(),
            ];
        })->filter()->values(); // Remove null entries and re-index

        return response()->json([
            'success'    => true,
            'properties' => $properties,
            'total'      => $properties->count(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Frontend Page 3: Property Detail
     * Only show room types that have at least one rate plan covering "today"
     * (since no specific dates are passed on the detail page — dates come later
     * on the room-selection page where AvailabilityController does exact filtering).
     */
    public function show($id)
    {
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $property = Property::published()
            ->with([
                'images' => fn($q) => $q->orderBy('sort_order'),
                // Only eager-load room types that have a rate plan active today
                'roomTypes' => fn($q) => $q->whereHas('ratePlans', function ($rp) use ($today, $tomorrow) {
                    $rp->where('start_date', '<=', $today->format('Y-m-d'))
                       ->where('end_date',   '>=', $tomorrow->format('Y-m-d'));
                })->orderBy('base_price'),
                'roomTypes.amenities',
                'roomTypes.ratePlans',
                'reviews'  => fn($q) => $q->where('status', 'approved')->latest()->limit(10),
                'reviews.guest',
            ])
            ->findOrFail($id);

        return response()->json([
            'success'  => true,
            'property' => [
                'id'          => $property->id,
                'name'        => $property->name,
                'description' => $property->description,
                'location'    => $property->location,
                'city'        => $property->city    ?? 'Unknown City',
                'state'       => $property->state   ?? 'Unknown State',
                'country'     => $property->country ?? 'UK',
                'address'     => $property->address,
                'postal_code' => $property->postal_code,
                'latitude'    => $property->latitude ?? 32.7157,
                'longitude'   => $property->longitude ?? -117.1611,
                'images'      => $property->images->map(fn($img) => [
                    'id'         => $img->id,
                    'url'        => $img->image_url,
                    'is_primary' => $img->is_primary,
                ]),
                'room_types' => $property->roomTypes->map(function ($room) {
                    // Show the earliest and latest sale dates so the frontend
                    // can hint to guests when the room is available
                    $rateDates = $room->ratePlans->sortBy('start_date');
                    $saleFrom  = $rateDates->first()?->start_date;
                    $saleTo    = $rateDates->last()?->end_date;

                    return [
                        'id'            => $room->id,
                        'name'          => $room->name,
                        'description'   => $room->description,
                        'base_price'    => number_format($room->base_price, 2),
                        'max_occupancy' => $room->max_occupancy,
                        'amenities'     => $room->amenities->pluck('name'),
                        'available_from' => $saleFrom,
                        'available_to'   => $saleTo,
                    ];
                }),
                'average_rating' => round($property->average_rating ?? 0, 1),
                'total_reviews'  => $property->reviews->count(),
                'review_count'   => $property->reviews->count(),
                'reviews'        => $property->reviews->map(fn($review) => [
                    'id'          => $review->id,
                    'rating'      => $review->rating,
                    'comment'     => $review->comment,
                    'guest_name'  => $review->guest?->first_name ?? $review->guest_name,
                    'created_at'  => $review->created_at->format('F Y'),
                    'staff_reply' => $review->staff_reply,
                ]),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}

