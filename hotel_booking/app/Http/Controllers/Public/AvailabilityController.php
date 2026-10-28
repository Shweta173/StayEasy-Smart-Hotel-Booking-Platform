<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\BookingHold;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Frontend Page 4: Room Selection / Availability
     * Check availability and get pricing for specific dates
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'sometimes|integer|min:1',
            'num_rooms' => 'sometimes|integer|min:1',
        ]);

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $guests = $validated['guests'] ?? 1;
        $numRooms = $validated['num_rooms'] ?? 1;
        $nights = $checkIn->diffInDays($checkOut);
        
        // Calculate guests per room for filtering
        $guestsPerRoom = ceil($guests / $numRooms);

        // Get all room types for this property
        $roomTypes = RoomType::where('property_id', $validated['property_id'])
            ->with(['amenities', 'ratePlans'])
            // Filter by guests per room, not total guests
            ->where('max_occupancy', '>=', $guestsPerRoom)
            ->get();

        $availableRooms = [];

        foreach ($roomTypes as $roomType) {
            // Check availability (includes rate plan window validation)
            $availability = $this->availabilityService->checkAvailability(
                $roomType->id,
                $checkIn->format('Y-m-d'),
                $checkOut->format('Y-m-d')
            );

            // Skip rooms outside their rate plan window entirely
            if (isset($availability['rate_plan_valid']) && $availability['rate_plan_valid'] === false) {
                continue;
            }

            // Get the active rate plan for this date range
            // The rate plan must cover from check-in through the last night (checkout - 1 day)
            $lastNight = $checkOut->copy()->subDay();
            $activePlan = $roomType->ratePlans->first(function($plan) use ($checkIn, $lastNight) {
                $planStart = Carbon::parse($plan->start_date);
                $planEnd = Carbon::parse($plan->end_date);
                return $planStart->lte($checkIn) && $planEnd->gte($lastNight);
            });

            // Check minimum nights requirement
            $minNights = $activePlan ? $activePlan->min_nights : null;
            $minNightsError = null;
            
            if ($minNights && $nights < $minNights) {
                $minNightsError = "Minimum {$minNights} " . ($minNights === 1 ? 'night' : 'nights') . " required";
            }

            // Check if enough rooms are available for the requested quantity
            if ($availability['available'] && $availability['available_units'] >= $numRooms) {
                // Calculate pricing for the stay (including number of rooms)
                $pricing = $this->calculatePricing($roomType, $checkIn, $checkOut, $numRooms);

                $availableRooms[] = [
                    'room_type_id' => $roomType->id,
                    'name' => $roomType->name,
                    'description' => $roomType->description,
                    'max_occupancy' => $roomType->max_occupancy,
                    'available_units' => $availability['available_units'],
                    'min_nights' => $minNights,
                    'min_nights_error' => $minNightsError,
                    'is_bookable' => $minNightsError === null,
                    'amenities' => $roomType->amenities->map(function ($amenity) {
                        return [
                            'id' => $amenity->id,
                            'name' => $amenity->name,
                            'icon' => $amenity->icon,
                        ];
                    }),
                    'pricing' => $pricing,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'nights' => $nights,
            'guests' => $guests,
            'num_rooms' => $numRooms,
            'available_rooms' => $availableRooms,
        ]);
    }

    /**
     * Get pricing calendar for a room type
     */
    public function calendar(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $roomType = RoomType::findOrFail($validated['room_type_id']);
        $startDate = Carbon::parse($validated['month'] . '-01');
        $endDate = $startDate->copy()->endOfMonth();

        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $price = $roomType->getPriceForDate($currentDate);
            
            // Check availability for single night
            $nextDay = $currentDate->copy()->addDay();
            $availability = $this->availabilityService->checkAvailability(
                $roomType->id,
                $currentDate->format('Y-m-d'),
                $nextDay->format('Y-m-d')
            );

            $calendar[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('D'),
                'price' => number_format($price, 2),
                'available' => $availability['available'],
                'available_units' => $availability['available_units'],
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'room_type' => [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'base_price' => number_format($roomType->base_price, 2),
            ],
            'month' => $startDate->format('F Y'),
            'calendar' => $calendar,
        ]);
    }

    /**
     * Search availability across properties
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'location' => 'nullable|string',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'sometimes|integer|min:1|max:20',
        ]);

        $available = $this->availabilityService->searchAvailability($validated);

        return response()->json([
            'success' => true,
            'search_criteria' => $validated,
            'total_results' => count($available),
            'available_rooms' => $available,
        ]);
    }

    /**
     * Calculate pricing for a stay (Backend calculates, not frontend!)
     */
    private function calculatePricing(RoomType $roomType, Carbon $checkIn, Carbon $checkOut, int $numRooms = 1): array
    {
        $nights = $checkIn->diffInDays($checkOut);
        $currentDate = $checkIn->copy();
        $nightlyRates = [];
        $subtotal = 0;

        // Calculate price for each night
        while ($currentDate->lt($checkOut)) {
            $price = $roomType->getPriceForDate($currentDate);
            $nightlyRates[] = [
                'date' => $currentDate->format('Y-m-d'),
                'price' => number_format($price, 2),
            ];
            $subtotal += $price;
            $currentDate->addDay();
        }

        // Multiply by number of rooms
        $subtotal = $subtotal * $numRooms;

        // Calculate taxes (using settings)
        $settings = Setting::getSettings();
        $taxRate = (float) ($settings->default_vat_rate ?? 10.00) / 100;
        $taxTotal = $subtotal * $taxRate;

        $grandTotal = $subtotal + $taxTotal;

        return [
            'nights' => $nights,
            'rooms' => $numRooms,
            'nightly_rates' => $nightlyRates,
            'subtotal' => number_format($subtotal, 2),
            'tax_rate' => ($taxRate * 100) . '%',
            'tax_total' => number_format($taxTotal, 2),
            'service_fee' => '0.00',
            'grand_total' => number_format($grandTotal, 2),
            'currency' => 'USD',
        ];
    }

    /**
     * Clean up expired holds (can be called by frontend to refresh availability)
     */
    public function cleanupHolds(Request $request)
    {
        try {
            BookingHold::where('status', 'pending')
                ->where('expires_at', '<=', now())
                ->update(['status' => 'expired']);

            return response()->json([
                'success' => true,
                'message' => 'Expired holds cleaned up'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

