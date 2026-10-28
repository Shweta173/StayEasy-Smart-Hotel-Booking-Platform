<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\Guest;
use App\Models\Order;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class BookingService
{
    use ApiResponse;
    
    protected $availabilityService;
    protected $orderService;

    public function __construct(
        AvailabilityService $availabilityService,
        OrderService $orderService
    ) {
        $this->availabilityService = $availabilityService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'property_id' => $request->input('property') ?: $request->input('property_id'),
            'check_in_from' => $request->input('from_date') ?: $request->input('check_in_from'),
            'check_in_to' => $request->input('to_date') ?: $request->input('check_in_to'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 20),
        ];

        $bookings = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'bookings' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
            ], 'Bookings retrieved successfully');
        }

        // Return view for web requests
        $stats = $this->getBookingStatistics();
        $properties = Property::select('id', 'name')->orderBy('name')->get();
        return view('admin.bookings', compact('bookings', 'stats', 'properties'));
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::with([
            'property',
            'roomType',
            'guest',
            'order.orderItems',
            'order.payments',
            'order.invoice'
        ])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($booking, 'Booking retrieved successfully');
        }

        // Return view for web requests
        $settings = Setting::getSettings();
        return view('admin.booking-detail', compact('booking', 'settings'));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:confirmed,checked_in,checked_out,cancelled,no_show',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $booking = Booking::findOrFail($id);
            $result = $this->changeBookingStatus($booking, $request->status, $request->notes);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($result, 'Booking status updated successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.bookings.show', $id)
                ->with('success', 'Booking status updated successfully');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e->errors());
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $stats = $this->getStatistics($startDate, $endDate);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($stats, 'Statistics retrieved successfully');
        }

        // Return view for web requests
        return view('admin.reports', compact('stats'));
    }

    public function todayActivity(Request $request)
    {
        $activity = $this->getTodayActivity();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($activity, 'Today activity retrieved successfully');
        }

        // Return view for web requests
        return view('admin.dashboard', compact('activity'));
    }

    public function handleCheckIn(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $booking = $this->checkIn($booking);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($booking, 'Guest checked in successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.bookings.show', $id)
                ->with('success', 'Guest checked in successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function handleCheckOut(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $booking = $this->checkOut($booking);

            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->successResponse($booking, 'Guest checked out successfully');
            }

            // Return redirect for web requests
            return redirect()->route('admin.bookings.show', $id)
                ->with('success', 'Guest checked out successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse($e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function list(array $filters = [])
    {
        $query = Booking::with([
            'property' => function($query) {
                $query->select('properties.id', 'properties.name', 'properties.city', 'properties.state');
            },
            'roomType' => function($query) {
                $query->select('room_types.id', 'room_types.name');
            },
            'guest' => function($query) {
                $query->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email', 'guests.phone');
            },
            'order.payments'
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by property
        if (!empty($filters['property_id'])) {
            $query->whereHas('roomType', function($q) use ($filters) {
                $q->where('property_id', $filters['property_id']);
            });
        }

        // Filter by date range
        if (!empty($filters['check_in_from'])) {
            $query->where('check_in', '>=', $filters['check_in_from']);
        }

        if (!empty($filters['check_in_to'])) {
            $query->where('check_in', '<=', $filters['check_in_to']);
        }

        // Search by booking reference or guest name/email/phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('booking_reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('guest', function($gq) use ($search) {
                      $gq->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    protected function getBookingStatistics(): array
    {
        $today = now()->toDateString();
        
        return [
            'total' => Booking::count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'checked_in' => Booking::where('status', 'checked_in')->count(),
            'checked_out' => Booking::where('status', 'checked_out')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'today_checkins' => Booking::where('check_in', $today)->where('status', 'confirmed')->count(),
            'today_checkouts' => Booking::where('check_out', $today)->where('status', 'checked_in')->count(),
        ];
    }

    protected function changeBookingStatus(Booking $booking, string $status, ?string $notes = null): array
    {
        // Check if booking can be modified
        if (in_array($booking->status, ['cancelled', 'checked_out']) && $status !== 'cancelled') {
            throw new \Exception('Cannot modify a cancelled or checked out booking');
        }

        return DB::transaction(function () use ($booking, $status, $notes) {
            $oldStatus = $booking->status;
            $booking->status = $status;
            
            if ($notes) {
                $booking->special_requests = $notes;
            }

            $booking->save();

            // If cancelling, handle refund logic
            if ($status === 'cancelled') {
                // Refund logic handled separately
            }

            return [
                'booking' => $booking->fresh(['property', 'roomType', 'guest']),
                'old_status' => $oldStatus,
                'new_status' => $status,
            ];
        });
    }

    protected function getStatistics(string $startDate, string $endDate): array
    {
        $stats = [
            'total_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'checked_in' => Booking::where('status', 'checked_in')->whereBetween('check_in', [$startDate, $endDate])->count(),
            'checked_out' => Booking::where('status', 'checked_out')->whereBetween('check_out', [$startDate, $endDate])->count(),
            'cancelled' => Booking::where('status', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::where('status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('grand_total_inc_vat'),
            'upcoming_checkins' => Booking::where('status', 'confirmed')
                ->where('check_in', '>=', now()->toDateString())
                ->where('check_in', '<=', now()->addDays(7)->toDateString())
                ->count(),
            'upcoming_checkouts' => Booking::where('status', 'checked_in')
                ->where('check_out', '>=', now()->toDateString())
                ->where('check_out', '<=', now()->addDays(7)->toDateString())
                ->count(),
        ];

        // Bookings by status
        $bookingsByStatus = Booking::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Revenue by property
        $revenueByProperty = Order::select('bookings.property_id', 'properties.name', DB::raw('sum(orders.grand_total) as revenue'))
            ->join('bookings', 'orders.booking_id', '=', 'bookings.id')
            ->join('properties', 'bookings.property_id', '=', 'properties.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('bookings.property_id', 'properties.name')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();

        return [
            'statistics' => $stats,
            'bookings_by_status' => $bookingsByStatus,
            'revenue_by_property' => $revenueByProperty,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Get today's activity (arrivals and departures)
     */
    protected function getTodayActivity(): array
    {
        $today = now()->toDateString();

        $arrivals = Booking::with([
            'property' => function($query) {
                $query->select('properties.id', 'properties.name');
            },
            'roomType' => function($query) {
                $query->select('room_types.id', 'room_types.name');
            },
            'guest' => function($query) {
                $query->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email', 'guests.phone');
            }
        ])
            ->where('check_in', $today)
            ->where('status', 'confirmed')
            ->get();

        $departures = Booking::with([
            'property' => function($query) {
                $query->select('properties.id', 'properties.name');
            },
            'roomType' => function($query) {
                $query->select('room_types.id', 'room_types.name');
            },
            'guest' => function($query) {
                $query->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email', 'guests.phone');
            }
        ])
            ->where('check_out', $today)
            ->where('status', 'checked_in')
            ->get();

        return [
            'date' => $today,
            'arrivals' => $arrivals,
            'departures' => $departures,
            'counts' => [
                'arrivals' => $arrivals->count(),
                'departures' => $departures->count(),
            ],
        ];
    }

    public function createHoldAndOrder(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // If user is authenticated, use their guest ID directly
            // Otherwise, find or create guest by email
            if (auth('sanctum')->check()) {
                $guest = auth('sanctum')->user();
                
                \Log::info('Creating hold for authenticated user', [
                    'guest_id' => $guest->id,
                    'guest_email' => $guest->email,
                    'guest_name' => $guest->first_name . ' ' . $guest->last_name,
                ]);
            } else {
                $guest = $this->findOrCreateGuest($data['guest']);
                
                \Log::info('Creating hold for guest (not authenticated)', [
                    'guest_id' => $guest->id,
                    'guest_email' => $guest->email,
                ]);
            }

            // Extract number of rooms and guests
            $numRooms = isset($data['num_rooms']) ? (int) $data['num_rooms'] : 1;
            $numGuests = isset($data['num_guests']) ? (int) $data['num_guests'] : 1;

            // Create hold with database locking (prevents double-booking)
            $hold = $this->availabilityService->createHold(
                $data['room_type_id'],
                $data['check_in'],
                $data['check_out'],
                $guest->id,
                $numRooms, // Pass number of rooms
                15, // 15 minutes hold
                $numGuests // Pass number of guests
            );

            // Create order from the hold
            $order = $this->orderService->createOrderFromHold($hold);

            return [
                'hold' => $hold->load('roomType.property'),
                'order' => $order->load('items'),
                'guest' => $guest,
            ];
        });
    }

    
    public function confirmBooking(BookingHold $hold, Order $order): Booking
    {
        return DB::transaction(function () use ($hold, $order) {
            // Update hold status
            $hold->update(['status' => 'confirmed']);

            // Create confirmed booking (booking_reference auto-generated by model)
            $booking = Booking::create([
                'room_type_id' => $hold->room_type_id,
                'check_in' => $hold->check_in,
                'check_out' => $hold->check_out,
                'guest_id' => $hold->guest_id,
                'booking_hold_id' => $hold->id,
                'status' => 'confirmed',
                'num_guests' => $hold->num_guests ?? 1,
                'special_requests' => null,
            ]);

            // Link order to booking
            $order->update(['booking_id' => $booking->id]);

            return $booking->load(['roomType.property', 'guest', 'order']);
        });
    }

    
    public function validateRoomAvailability(int $roomTypeId, $checkIn, $checkOut, ?int $excludeHoldId = null): bool
    {
        $roomType = RoomType::findOrFail($roomTypeId);
        
        // *** CHECK 0: Room Type Must Have Units Set ***
        if ($roomType->total_units === null || $roomType->total_units <= 0) {
            \Log::warning('Room type has invalid total_units', [
                'room_type_id' => $roomTypeId,
                'total_units' => $roomType->total_units,
            ]);
            return false; // Can't validate without knowing how many rooms exist
        }
        
       
        $ratePlans = RatePlan::where('room_type_id', $roomTypeId)
            ->get();
        
        if ($ratePlans->count() > 0) {
            // Check if the booking dates fall within ANY active rate plan
            $hasValidRatePlan = $ratePlans->filter(function ($plan) use ($checkIn, $checkOut) {
                $planStart = Carbon::parse($plan->start_date);
                $planEnd = Carbon::parse($plan->end_date);
                $bookingStart = Carbon::parse($checkIn);
                $bookingEnd = Carbon::parse($checkOut)->subDay(); // Last night, not checkout day
                
                // Booking must be completely within the rate plan period
                return $bookingStart >= $planStart && 
                       $bookingEnd <= $planEnd;
            })->isNotEmpty();
            
            if (!$hasValidRatePlan) {
                \Log::warning('Booking dates outside rate plan range', [
                    'room_type_id' => $roomTypeId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'rate_plans' => $ratePlans->map(fn($p) => [
                        'start' => $p->start_date,
                        'end' => $p->end_date
                    ])
                ]);
                return false; // Dates are outside all rate plan ranges
            }
        }
        
        
        $overlappingBookings = Booking::where('room_type_id', $roomTypeId)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                //
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->count();
        
        
        $overlappingHolds = BookingHold::where('room_type_id', $roomTypeId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->when($excludeHoldId, function ($query) use ($excludeHoldId) {
                $query->where('id', '!=', $excludeHoldId);
            })
            ->where(function ($query) use ($checkIn, $checkOut) {
                // A hold overlaps if:
                // - Its check-in is before our check-out AND
                // - Its check-out is after our check-in
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->count();
        
        $totalConflicts = $overlappingBookings + $overlappingHolds;
        
        // Check if we have enough rooms available
        $hasCapacity = $totalConflicts < $roomType->total_units;
        
        if (!$hasCapacity) {
            \Log::warning('Insufficient room capacity', [
                'room_type_id' => $roomTypeId,
                'total_conflicts' => $totalConflicts,
                'total_units' => $roomType->total_units,
            ]);
        }
        
        return $hasCapacity;
    }

    
    private function findOrCreateGuest(array $guestData): Guest
    {
        $guest = Guest::where('email', $guestData['email'])->first();

        if (!$guest) {
            $guest = Guest::create([
                'first_name' => $guestData['first_name'],
                'last_name' => $guestData['last_name'],
                'email' => $guestData['email'],
                'phone' => $guestData['phone'] ?? null,
            ]);
        }

        return $guest;
    }

    
    public function getBookingByReference(string $reference, string $email): ?Booking
    {
        return Booking::where('booking_reference', $reference)
            ->whereHas('guest', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->with(['roomType.property', 'order.items', 'order.invoice'])
            ->first();
    }

    
    public function cancelBooking(Booking $booking, string $reason, string $cancelledBy = 'guest'): array
    {
        return DB::transaction(function () use ($booking, $reason, $cancelledBy) {
            // Check if booking can be cancelled
            if (!in_array($booking->status, ['confirmed', 'pending'])) {
                throw new \Exception('This booking cannot be cancelled. Status: ' . $booking->status);
            }

            // Calculate refund amount based on cancellation policy
            $checkInDate = Carbon::parse($booking->check_in);
            $hoursUntilCheckIn = now()->diffInHours($checkInDate, false);
            
            $refundPercentage = 100; // Default full refund
            $refundReason = 'Full refund - cancelled more than 24 hours before check-in';
            
            if ($hoursUntilCheckIn < 24 && $hoursUntilCheckIn > 0) {
                $refundPercentage = 50; // 50% refund if less than 24 hours
                $refundReason = '50% refund - cancelled within 24 hours of check-in';
            } elseif ($hoursUntilCheckIn <= 0) {
                $refundPercentage = 0; // No refund if check-in has passed
                $refundReason = 'No refund - check-in date has passed';
            }

            $totalAmount = $booking->order ? $booking->order->grand_total_inc_vat : 0;
            $refundAmount = ($totalAmount * $refundPercentage) / 100;

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
                'refund_amount' => $refundAmount,
                'refund_percentage' => $refundPercentage,
                'refund_status' => $refundAmount > 0 ? 'pending' : 'not_applicable',
            ]);

            return [
                'booking' => $booking->fresh()->load(['roomType.property', 'guest', 'order']),
                'refund_amount' => $refundAmount,
                'refund_percentage' => $refundPercentage,
                'refund_reason' => $refundReason,
            ];
        });
    }

    /**
     * Check-in a booking
     */
    public function checkIn(Booking $booking): Booking
    {
        if (!$booking->canCheckIn()) {
            $today = now()->toDateString();
            $checkInDate = Carbon::parse($booking->check_in)->toDateString();
            
            if ($booking->status !== 'confirmed') {
                throw new \Exception('Booking cannot be checked in. Current status: ' . $booking->status);
            }
            
            if ($today < $checkInDate) {
                $checkInFormatted = Carbon::parse($booking->check_in)->format('M d, Y');
                throw new \Exception("Check-in is only allowed on the check-in date: {$checkInFormatted}. It's too early to check in.");
            }
            
            if ($today > $checkInDate) {
                $checkInFormatted = Carbon::parse($booking->check_in)->format('M d, Y');
                throw new \Exception("Check-in date ({$checkInFormatted}) has passed. Please contact management.");
            }
        }

        $booking->update(['status' => 'checked_in']);

        return $booking->fresh();
    }

    /**
     * Check-out a booking
     */
    public function checkOut(Booking $booking): Booking
    {
        if (!$booking->canCheckOut()) {
            throw new \Exception('Booking cannot be checked out. Current status: ' . $booking->status);
        }

        $booking->update(['status' => 'checked_out']);

        return $booking->fresh();
    }
}

