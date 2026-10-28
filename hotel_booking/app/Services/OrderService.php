<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Booking;
use App\Models\BookingHold;
use App\Models\RoomType;
use App\Models\Property;
use App\Models\Guest;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService
{
    use ApiResponse;

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'date_from' => $request->input('from_date') ?: $request->input('date_from'),
            'date_to' => $request->input('to_date') ?: $request->input('date_to'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 20),
        ];

        $orders = $this->list($filters);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ], 'Orders retrieved successfully');
        }

        // Return view for web requests
        $stats = $this->getOrderStatistics();
        return view('admin.orders', compact('orders', 'stats'));
    }

    public function show(Request $request, $id)
    {
        $order = Order::with([
            'booking.property',
            'booking.roomType',
            'booking.guest',
            'orderItems',
            'payments',
            'invoice'
        ])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($order, 'Order retrieved successfully');
        }

        // Return view for web requests
        return view('admin.orders.show', compact('order'));
    }

    public function statistics(Request $request)
    {
        // Get date range - default to last 30 days
        $fromDate = $request->get('from_date', now()->subDays(30)->toDateString());
        $toDate = $request->get('to_date', now()->toDateString());

        // Ensure dates are in correct format
        $fromDate = Carbon::parse($fromDate)->toDateString();
        $toDate = Carbon::parse($toDate)->toDateString();

        // Get all statistics
        $statsData = $this->getStatistics($fromDate, $toDate);
        $stats = $statsData['statistics'];
        
        // Transform daily_revenue for chart
        $dailyRevenue = $statsData['daily_revenue'];
        $revenueData = [
            'labels' => $dailyRevenue->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            })->toArray(),
            'values' => $dailyRevenue->pluck('revenue')->map(function($value) {
                return (float) $value;
            })->toArray()
        ];
        
        // Ensure we have at least some data points for the chart
        if (empty($revenueData['labels'])) {
            // Generate date range
            $start = Carbon::parse($fromDate);
            $end = Carbon::parse($toDate);
            $revenueData = [
                'labels' => [],
                'values' => []
            ];
            while ($start->lte($end)) {
                $revenueData['labels'][] = $start->format('M d');
                $revenueData['values'][] = 0;
                $start->addDay();
            }
        }
        
        $metrics = $this->getMetrics($fromDate, $toDate);
        $topRoomTypes = $this->getTopRoomTypes($fromDate, $toDate);
        $propertyOccupancy = $this->getPropertyOccupancy($fromDate, $toDate);
        $bookingSources = $this->getBookingSources($fromDate, $toDate);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'stats' => $stats,
                'revenueData' => $revenueData,
                'metrics' => $metrics,
                'topRoomTypes' => $topRoomTypes,
                'propertyOccupancy' => $propertyOccupancy,
                'bookingSources' => $bookingSources,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ], 'Statistics retrieved successfully');
        }

        // Return view for web requests
        return view('admin.reports', compact('stats', 'revenueData', 'metrics', 'topRoomTypes', 'propertyOccupancy', 'bookingSources', 'fromDate', 'toDate'));
    }

    protected function list(array $filters = [])
    {
        $query = Order::with([
            'guest' => function($q) {
                $q->select('guests.id', 'guests.first_name', 'guests.last_name', 'guests.email');
            },
            'booking' => function($query) {
                $query->with([
                    'property' => function($q) {
                        $q->select('properties.id', 'properties.name');
                    },
                    'roomType' => function($q) {
                        $q->select('room_types.id', 'room_types.name');
                    }
                ]);
            },
            'payments',
            'invoice'
        ])
        ->whereNotNull('booking_id') // Only show orders with bookings
        ->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Search by order number or guest email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('booking.guest', function($gq) use ($search) {
                      $gq->where('email', 'LIKE', "%{$search}%")
                        ->orWhere('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $perPage = $filters['per_page'] ?? 20;
        return $query->paginate($perPage);
    }

    protected function getOrderStatistics(): array
    {
        return [
            'total_orders' => Order::count(),
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('grand_total_inc_vat'),
        ];
    }

    protected function getStatistics(string $startDate, string $endDate): array
    {
        $stats = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed_orders' => Order::where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders' => Order::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::where('status', 'paid')->whereBetween('created_at', [$startDate, $endDate])->sum('grand_total_inc_vat'),
            'average_order_value' => Order::where('status', 'paid')->whereBetween('created_at', [$startDate, $endDate])->avg('grand_total_inc_vat'),
        ];

        // Daily revenue for chart
        $dailyRevenue = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('sum(grand_total_inc_vat) as revenue'),
                DB::raw('count(*) as orders')
            )
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'statistics' => $stats,
            'daily_revenue' => $dailyRevenue,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    protected function getMetrics(string $startDate, string $endDate): array
    {
        // Get total revenue for the period
        $totalRevenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('grand_total_inc_vat');

        // Get number of bookings
        $bookingsCount = Booking::where('status', 'confirmed')
            ->whereBetween('check_in', [$startDate, $endDate])
            ->count();

        // Calculate days in period
        $daysInPeriod = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);

        // Calculate ADR (Average Daily Rate) = Total Revenue / Number of Bookings
        $adr = $bookingsCount > 0 ? $totalRevenue / $bookingsCount : 0;

        // Estimate occupancy (simplified - actual calculation would need room inventory)
        // For now, we'll use a simple metric: bookings per day
        $avgBookingsPerDay = $bookingsCount / $daysInPeriod;
        $occupancyRate = min(100, $avgBookingsPerDay * 10); // Simplified calculation

        // Calculate RevPAR (Revenue Per Available Room)
        // RevPAR = Total Revenue / (Available Rooms × Days)
        // Simplified: use occupancy rate as proxy
        $revpar = $adr * ($occupancyRate / 100);

        // Get previous period for comparison
        $previousStart = Carbon::parse($startDate)->subDays($daysInPeriod)->toDateString();
        $previousEnd = Carbon::parse($startDate)->subDay()->toDateString();

        $previousRevenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('grand_total_inc_vat');

        $previousBookings = Booking::where('status', 'confirmed')
            ->whereBetween('check_in', [$previousStart, $previousEnd])
            ->count();

        $previousAdr = $previousBookings > 0 ? $previousRevenue / $previousBookings : 0;
        $previousAvgBookingsPerDay = $previousBookings / $daysInPeriod;
        $previousOccupancy = min(100, $previousAvgBookingsPerDay * 10);
        $previousRevpar = $previousAdr * ($previousOccupancy / 100);

        // Calculate percentage changes
        $revenueChange = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        $occupancyChange = $previousOccupancy > 0 ? $occupancyRate - $previousOccupancy : 0;
        $adrChange = $previousAdr > 0 ? (($adr - $previousAdr) / $previousAdr) * 100 : 0;
        $revparChange = $previousRevpar > 0 ? (($revpar - $previousRevpar) / $previousRevpar) * 100 : 0;

        return [
            'occupancy_rate' => $occupancyRate,
            'occupancy_change' => $occupancyChange,
            'revpar' => $revpar,
            'revpar_change' => $revparChange,
            'total_revenue' => $totalRevenue,
            'revenue_change' => $revenueChange,
            'adr' => $adr,
            'adr_change' => $adrChange,
        ];
    }

    
    protected function getTopRoomTypes(string $startDate, string $endDate): array
    {
        $topRoomTypes = DB::table('bookings')
            ->join('room_types', 'bookings.room_type_id', '=', 'room_types.id')
            ->join('properties', 'room_types.property_id', '=', 'properties.id')
            ->join('orders', 'bookings.id', '=', 'orders.booking_id')
            ->select(
                'room_types.name',
                'properties.name as property',
                DB::raw('COUNT(bookings.id) as bookings'),
                DB::raw('SUM(orders.grand_total_inc_vat) as revenue')
            )
            ->where('bookings.status', 'confirmed')
            ->where('orders.status', 'paid')
            ->whereBetween('bookings.check_in', [$startDate, $endDate])
            ->groupBy('room_types.id', 'room_types.name', 'properties.name')
            ->orderByDesc('bookings')
            ->limit(5)
            ->get()
            ->toArray();

        // Calculate percentages
        $totalBookings = array_sum(array_column($topRoomTypes, 'bookings'));
        
        return array_map(function($item) use ($totalBookings) {
            return [
                'name' => $item->name,
                'property' => $item->property,
                'bookings' => $item->bookings,
                'revenue' => $item->revenue,
                'percentage' => $totalBookings > 0 ? ($item->bookings / $totalBookings) * 100 : 0
            ];
        }, $topRoomTypes);
    }

    
    protected function getPropertyOccupancy(string $startDate, string $endDate): array
    {
        $propertyOccupancy = DB::table('properties')
            ->leftJoin('room_types', 'properties.id', '=', 'room_types.property_id')
            ->leftJoin('bookings', function($join) use ($startDate, $endDate) {
                $join->on('room_types.id', '=', 'bookings.room_type_id')
                    ->where('bookings.status', '=', 'confirmed')
                    ->whereBetween('bookings.check_in', [$startDate, $endDate]);
            })
            ->leftJoin('orders', function($join) {
                $join->on('bookings.id', '=', 'orders.booking_id')
                    ->where('orders.status', '=', 'paid');
            })
            ->select(
                'properties.name',
                'properties.location',
                'properties.id',
                DB::raw('COUNT(DISTINCT room_types.id) as total_units'),
                DB::raw('COUNT(DISTINCT bookings.id) as booked_units'),
                DB::raw('COALESCE(SUM(orders.grand_total_inc_vat), 0) as revenue')
            )
            ->groupBy('properties.id', 'properties.name', 'properties.location')
            ->orderByDesc('booked_units')
            ->get()
            ->toArray();

        // Calculate occupancy rate and RevPAR
        return array_map(function($item) {
            $totalUnits = $item->total_units > 0 ? $item->total_units : 1;
            $occupancyRate = ($item->booked_units / $totalUnits) * 100;
            $revpar = $totalUnits > 0 ? $item->revenue / $totalUnits : 0;
            
            return [
                'name' => $item->name,
                'location' => $item->location ?? 'N/A',
                'total_units' => $item->total_units,
                'booked_units' => $item->booked_units,
                'occupancy_rate' => min(100, $occupancyRate),
                'revenue' => $item->revenue,
                'revpar' => $revpar
            ];
        }, $propertyOccupancy);
    }

    protected function getBookingSources(string $startDate, string $endDate): array
    {
        // Get total paid bookings count
        $totalBookings = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('booking')
            ->count();

        // If no bookings exist, return sample data to show chart structure
        if ($totalBookings === 0) {
            return [
                'labels' => ['Direct Website', 'Phone', 'Email', 'Walk-in'],
                'values' => [0, 0, 0, 0]
            ];
        }

        // For now, show all bookings as Direct Website bookings
        // TODO: Add source tracking to bookings table if needed
        return [
            'labels' => ['Direct Website', 'Phone Reservation', 'Email Inquiry', 'Walk-in'],
            'values' => [
                $totalBookings, // All bookings shown as Direct Website
                0,
                0,
                0
            ]
        ];
    }

   
    private function getVatSettings(): array
    {
        $settings = Setting::getSettings();
        
        return [
            'rate' => (float) ($settings->default_vat_rate ?? 20.00),
            'prices_include_vat' => (bool) ($settings->prices_include_vat ?? true),
            'vat_registered' => (bool) ($settings->vat_registered ?? true),
        ];
    }
    
    public function createOrderFromHold(BookingHold $hold): Order
    {
        return DB::transaction(function () use ($hold) {
            $roomType = $hold->roomType;
            $checkIn = Carbon::parse($hold->check_in);
            $checkOut = Carbon::parse($hold->check_out);
            $nights = $checkIn->diffInDays($checkOut);
            $numRooms = $hold->num_rooms ?? 1; // Default to 1 if not set

            // Calculate nightly rates (base room prices per night per room)
            $nightlyRates = $this->calculateNightlyRates($roomType, $checkIn, $checkOut);
            
            // Calculate subtotal: Sum of nightly rates × number of rooms
            $perRoomSubtotal = array_sum(array_column($nightlyRates, 'price'));
            $subtotal = $perRoomSubtotal * $numRooms;

            // Get VAT settings
            $vatSettings = $this->getVatSettings();
            $taxRate = $vatSettings['rate'];
            $pricesIncludeVat = $vatSettings['prices_include_vat'];

            // ALWAYS add VAT on top of the subtotal
            $subtotalExVat = $subtotal;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $grandTotalIncVat = $subtotal + $taxAmount;

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            // Create order
            $order = Order::create([
                'booking_id' => null, // Will be set when booking is confirmed
                'guest_id' => $hold->guest_id,
                'order_number' => $orderNumber,
                'subtotal_ex_vat' => $subtotalExVat,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'vat_total' => $taxAmount,
                'service_fee' => 0,
                'processing_fee' => 0,
                'grand_total_inc_vat' => $grandTotalIncVat,
                'currency' => 'USD',
                'payment_method' => 'stripe',
                'status' => 'pending',
            ]);

            // Create order items for each night × number of rooms
            foreach ($nightlyRates as $nightRate) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'description' => $roomType->name . ' - ' . $nightRate['date'] . ' (' . $numRooms . ' room' . ($numRooms > 1 ? 's' : '') . ')',
                    'qty' => $numRooms,
                    'unit_price' => $nightRate['price'], // Price per room per night
                    'line_total' => $nightRate['price'] * $numRooms, // Total for all rooms this night
                    'item_type' => 'room_night',
                ]);
            }

            // Create tax item
            OrderItem::create([
                'order_id' => $order->id,
                'description' => 'Tax (' . $taxRate . '%)',
                'qty' => 1,
                'unit_price' => $taxAmount,
                'line_total' => $taxAmount,
                'item_type' => 'tax',
            ]);

            return $order->load('items');
        });
    }

    
    private function calculateNightlyRates(RoomType $roomType, Carbon $checkIn, Carbon $checkOut): array
    {
        $rates = [];
        $currentDate = $checkIn->copy();

        while ($currentDate->lt($checkOut)) {
            $price = $roomType->getPriceForDate($currentDate);
            
            $rates[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'price' => $price,
            ];

            $currentDate->addDay();
        }

        return $rates;
    }

    
    public function getPricingBreakdown(int $roomTypeId, string $checkIn, string $checkOut, int $numRooms = 1): array
    {
        $roomType = RoomType::findOrFail($roomTypeId);
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        
        $nightlyRates = $this->calculateNightlyRates($roomType, $checkInDate, $checkOutDate);
        
        // Calculate subtotal: (price per night) × (number of nights) × (number of rooms)
        $perRoomSubtotal = array_sum(array_column($nightlyRates, 'price'));
        $subtotal = $perRoomSubtotal * $numRooms;

        // Get VAT settings
        $vatSettings = $this->getVatSettings();
        $taxRate = $vatSettings['rate'];
        $pricesIncludeVat = $vatSettings['prices_include_vat'];

        // ALWAYS add VAT on top of the subtotal
        $subtotalExVat = $subtotal;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $grandTotal = $subtotal + $taxAmount;

        return [
            'room_type' => [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'property' => $roomType->property->name,
            ],
            'stay_details' => [
                'check_in' => $checkInDate->format('Y-m-d'),
                'check_out' => $checkOutDate->format('Y-m-d'),
                'nights' => count($nightlyRates),
                'num_rooms' => $numRooms,
            ],
            'nightly_rates' => array_map(function ($rate) use ($numRooms) {
                return [
                    'date' => $rate['date'],
                    'day_name' => $rate['day_name'],
                    'price_per_room' => number_format($rate['price'], 2),
                    'total_for_rooms' => number_format($rate['price'] * $numRooms, 2), // Price × num_rooms
                ];
            }, $nightlyRates),
            'pricing' => [
                'subtotal' => number_format($subtotal, 2), // Display price (may include VAT)
                'subtotal_ex_vat' => number_format($subtotalExVat, 2), // Net amount
                'per_room_subtotal' => number_format($perRoomSubtotal, 2),
                'tax_rate' => $taxRate . '%',
                'tax_amount' => number_format($taxAmount, 2),
                'prices_include_vat' => $pricesIncludeVat, // Tell frontend if VAT is included
                'service_fee' => '0.00',
                'processing_fee' => '0.00',
                'grand_total' => number_format($grandTotal, 2),
                'currency' => 'USD',
            ],
            'calculation_details' => [
                'formula' => '(Room Rate × Nights × Rooms) + Tax',
                'breakdown' => sprintf(
                    '($%s × %d nights × %d room%s) + $%s tax = $%s',
                    number_format($perRoomSubtotal / count($nightlyRates), 2),
                    count($nightlyRates),
                    $numRooms,
                    $numRooms > 1 ? 's' : '',
                    number_format($taxAmount, 2),
                    number_format($grandTotal, 2)
                ),
            ],
        ];
    }

    
    public function markOrderAsPaid(Order $order): Order
    {
        $order->update(['status' => 'paid']);
        return $order;
    }

    
    public function refundOrder(Order $order): Order
    {
        $order->update(['status' => 'refunded']);
        return $order;
    }
}
