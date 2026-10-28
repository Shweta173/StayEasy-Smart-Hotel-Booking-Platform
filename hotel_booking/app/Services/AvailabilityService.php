<?php

namespace App\Services;

use App\Models\RoomType;
use App\Models\Property;
use App\Models\BookingHold;
use App\Models\RatePlan;
use App\Models\Booking;
use App\Models\AvailabilityBlock;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AvailabilityService
{
    use ApiResponse;

    /**
     * Display availability calendar - handles request
     */
    public function calendar(Request $request)
    {
        // Get all properties with room types and related data
        $properties = Property::with([
            'roomTypes' => function($query) {
                $query->orderBy('name');
            },
            'roomTypes.amenities',
            'roomTypes.ratePlans'
        ])->orderBy('name')->get();

        // Clean up expired holds first
        $this->cleanupExpiredHolds();

        // Get all bookings for the calendar
        $bookings = Booking::with(['roomType', 'guest'])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'room_type_id' => $booking->room_type_id,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'status' => $booking->status,
                    'guest_name' => $booking->guest->name ?? 'N/A',
                ];
            });

        // Get all active holds for the calendar
        $holds = BookingHold::with(['roomType', 'guest'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get()
            ->map(function($hold) {
                return [
                    'id' => $hold->id,
                    'room_type_id' => $hold->room_type_id,
                    'check_in' => $hold->check_in->format('Y-m-d'),
                    'check_out' => $hold->check_out->format('Y-m-d'),
                    'status' => 'hold',
                    'guest_name' => $hold->guest->name ?? 'N/A',
                    'expires_at' => $hold->expires_at->format('Y-m-d H:i:s'),
                    'num_rooms' => $hold->num_rooms,
                ];
            });

        // Get all blocked dates
        $blockedDates = AvailabilityBlock::with(['roomType', 'blockedBy'])
            ->get()
            ->map(function($block) {
                return [
                    'id' => $block->id,
                    'room_type_id' => $block->room_type_id,
                    'start_date' => $block->start_date->format('Y-m-d'),
                    'end_date' => $block->end_date->format('Y-m-d'),
                    'reason' => $block->reason,
                    'blocked_by' => optional($block->blockedBy)->name ?? 'System',
                ];
            });

        // Get all rate plans
        $ratePlans = RatePlan::select(['id', 'room_type_id', 'start_date', 'end_date'])
            ->get()
            ->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'room_type_id' => $plan->room_type_id,
                    'start_date' => $plan->start_date,
                    'end_date' => $plan->end_date,
                ];
            });

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'properties' => $properties,
                'bookings' => $bookings,
                'holds' => $holds,
                'blockedDates' => $blockedDates,
                'ratePlans' => $ratePlans
            ], 'Availability retrieved successfully');
        }

        // Return view for web requests
        return view('admin.availability', compact('properties', 'bookings', 'holds', 'blockedDates', 'ratePlans'));
    }

    /**
     * Block dates - handles request
     */
    public function block(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'room_type_id' => 'required|exists:room_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $block = $this->createBlock($request->all());

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dates blocked successfully',
                    'data' => $block
                ], 201);
            }

            // Return redirect for web requests
            return redirect()->route('admin.availability')
                ->with('success', 'Dates blocked successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unblock dates - handles request
     */
    public function unblock(Request $request, $id)
    {
        try {
            $this->removeBlock($id);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Block removed successfully'
                ], 200);
            }

            // Return redirect for web requests
            return redirect()->route('admin.availability')
                ->with('success', 'Block removed successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function createHold(int $roomTypeId, string $checkIn, string $checkOut, int $guestId, int $numRooms = 1,int $holdMinutes = 15,int $numGuests = 1): BookingHold
    {
        // Check availability first
        $availability = $this->checkAvailability($roomTypeId, $checkIn, $checkOut);
        
        if (!$availability['available']) {
            throw new \Exception($availability['reason'] ?? 'Room type not available for selected dates');
        }
        
        if ($availability['available_units'] < $numRooms) {
            throw new \Exception("Only {$availability['available_units']} room(s) available. You requested {$numRooms}.");
        }
        
        // Create the hold
        $hold = BookingHold::create([
            'room_type_id' => $roomTypeId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guest_id' => $guestId,
            'num_rooms' => $numRooms,
            'num_guests' => $numGuests,
            'status' => 'pending',
            'expires_at' => now()->addMinutes($holdMinutes),
        ]);
        
        return $hold;
    }

    
    public function checkAvailability(int $roomTypeId, string $checkIn, string $checkOut): array
    {
        $roomType = RoomType::findOrFail($roomTypeId);
        
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        
        // Check if there's an active rate plan covering these dates
        // The rate plan must cover from check-in through the last night (checkout - 1 day)
        $lastNight = $checkOutDate->copy()->subDay();
        $hasValidRatePlan = $roomType->ratePlans()
            ->where('start_date', '<=', $checkInDate->format('Y-m-d'))
            ->where('end_date', '>=', $lastNight->format('Y-m-d'))
            ->exists();
        
        if (!$hasValidRatePlan) {
            return [
                'available' => false,
                'available_units' => 0,
                'rate_plan_valid' => false,
                'reason' => 'No rate plan available for these dates'
            ];
        }
        
        // Count bookings that overlap with the requested dates
        // A booking overlaps if: check_in < requested_check_out AND check_out > requested_check_in
        $bookedUnits = Booking::where('room_type_id', $roomTypeId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->count();
        
        // Count active holds (pending and not expired)
        $heldUnits = BookingHold::where('room_type_id', $roomTypeId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->sum('num_rooms'); // Sum because holds can be for multiple rooms
        
        // Check for blocked dates that overlap with the requested dates
        $hasBlockedDates = AvailabilityBlock::where('room_type_id', $roomTypeId)
            ->where('start_date', '<', $checkOut)
            ->where('end_date', '>=', $checkIn)
            ->exists();
        
        if ($hasBlockedDates) {
            return [
                'available' => false,
                'available_units' => 0,
                'rate_plan_valid' => true,
                'reason' => 'Dates are blocked'
            ];
        }
        
        // Calculate available units (subtract both bookings and active holds)
        $availableUnits = $roomType->total_units - $bookedUnits - $heldUnits;
        
        return [
            'available' => $availableUnits > 0,
            'available_units' => max(0, $availableUnits),
            'rate_plan_valid' => true,
            'total_units' => $roomType->total_units,
            'booked_units' => $bookedUnits,
            'held_units' => $heldUnits
        ];
    }

   
    public function searchAvailability(array $criteria): array
    {
        $checkIn = $criteria['check_in'];
        $checkOut = $criteria['check_out'];
        $guests = $criteria['guests'] ?? 1;
        $location = $criteria['location'] ?? null;
        
        // Query properties
        $query = Property::with([
            'roomTypes' => function($q) use ($guests) {
                $q->where('max_occupancy', '>=', $guests);
            },
            'roomTypes.amenities',
            'roomTypes.ratePlans'
        ]);
        
        // Filter by location if provided
        if ($location) {
            $query->where(function($q) use ($location) {
                $q->where('name', 'like', "%{$location}%")
                  ->orWhere('address', 'like', "%{$location}%")
                  ->orWhere('city', 'like', "%{$location}%")
                  ->orWhere('state', 'like', "%{$location}%")
                  ->orWhere('country', 'like', "%{$location}%");
            });
        }
        
        $properties = $query->get();
        $availableRooms = [];
        
        foreach ($properties as $property) {
            foreach ($property->roomTypes as $roomType) {
                // Check availability for this room type
                $availability = $this->checkAvailability(
                    $roomType->id,
                    $checkIn,
                    $checkOut
                );
                
                if ($availability['available']) {
                    $availableRooms[] = [
                        'property_id' => $property->id,
                        'property_name' => $property->name,
                        'property_address' => $property->address,
                        'property_city' => $property->city,
                        'room_type_id' => $roomType->id,
                        'room_type_name' => $roomType->name,
                        'description' => $roomType->description,
                        'max_occupancy' => $roomType->max_occupancy,
                        'base_price' => $roomType->base_price,
                        'available_units' => $availability['available_units'],
                        'amenities' => $roomType->amenities->map(function($amenity) {
                            return [
                                'id' => $amenity->id,
                                'name' => $amenity->name,
                                'icon' => $amenity->icon
                            ];
                        })
                    ];
                }
            }
        }
        
        return $availableRooms;
    }

    
    protected function createBlock(array $data): AvailabilityBlock
    {
        return AvailabilityBlock::create([
            'room_type_id' => $data['room_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? 'Manually blocked',
            'blocked_by' => auth()->guard('admin')->id(),
        ]);
    }

    
    protected function removeBlock(int $id): bool
    {
        $block = AvailabilityBlock::findOrFail($id);
        return $block->delete();
    }

    
    protected function cleanupExpiredHolds(): void
    {
        BookingHold::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    
    public function releaseHold(int $holdId): bool
    {
        $hold = BookingHold::findOrFail($holdId);
        
        if ($hold->status === 'pending') {
            $hold->update(['status' => 'expired']);
            return true;
        }
        
        return false;
    }

    
    public function listActiveHolds(): array
    {
        try {
            $holds = BookingHold::with(['roomType.property', 'guest'])
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->orderBy('expires_at', 'asc')
                ->get()
                ->map(function($hold) {
                    return [
                        'id' => $hold->id,
                        'room_type' => $hold->roomType->name,
                        'property' => $hold->roomType->property->name,
                        'guest_name' => $hold->guest->name,
                        'guest_email' => $hold->guest->email,
                        'check_in' => $hold->check_in->format('Y-m-d'),
                        'check_out' => $hold->check_out->format('Y-m-d'),
                        'num_rooms' => $hold->num_rooms,
                        'expires_at' => $hold->expires_at->format('Y-m-d H:i:s'),
                        'time_remaining' => $hold->expires_at->diffForHumans(),
                        'created_at' => $hold->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return $holds->toArray();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    
    public function releaseHoldById(int $id): array
    {
        try {
            $released = $this->releaseHold($id);

            if ($released) {
                return [
                    'success' => true,
                    'message' => 'Hold released successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Hold cannot be released (not in pending status)'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    
    public function cleanupAllExpiredHolds(): array
    {
        try {
            $expiredCount = BookingHold::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->count();

            BookingHold::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            return [
                'success' => true,
                'message' => "Cleaned up {$expiredCount} expired hold(s)",
                'count' => $expiredCount
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
