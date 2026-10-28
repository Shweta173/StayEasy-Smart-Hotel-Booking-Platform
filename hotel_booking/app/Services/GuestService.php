<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Booking;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class GuestService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'sort' => $request->input('sort', 'recent'),
            'per_page' => $request->input('per_page', 15),
        ];

        $guests = $this->list($filters);
        $stats = $this->getStatistics();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'guests' => $guests,
                'statistics' => $stats,
            ], 'Guests retrieved successfully');
        }

        // Return view for web requests
        return view('admin.guests', compact('guests', 'stats'));
    }

    
    public function show(Request $request, $id)
    {
        $guest = Guest::with(['bookings.roomType.property', 'bookings.order'])->findOrFail($id);

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($guest, 'Guest retrieved successfully');
        }

        // Return view for web requests (if needed)
        return response()->json([
            'msgtype' => 'success',
            'data' => $guest
        ]);
    }

    
    public function bookings(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);
        $bookings = Booking::with(['roomType.property', 'order'])
            ->where('guest_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Always return JSON for API/AJAX requests
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return $this->successResponse([
                'guest' => $guest,
                'bookings' => $bookings
            ], 'Guest bookings retrieved successfully');
        }

        // Return view for web requests
        return view('admin.guest-bookings', compact('guest', 'bookings'));
    }

   
    protected function list(array $filters = [])
    {
        $query = Guest::withCount('bookings')
            ->withSum(['bookings as total_revenue' => function($query) {
                $query->join('orders', 'bookings.id', '=', 'orders.booking_id')
                      ->where('orders.status', 'paid');
            }], 'orders.grand_total_inc_vat');

        // Search by name, email, or phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by guest type based on booking count
        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'new':
                    $query->has('bookings', '=', 1);
                    break;
                case 'regular':
                    $query->has('bookings', '>=', 2)->has('bookings', '<=', 4);
                    break;
                case 'vip':
                    $query->has('bookings', '>=', 5);
                    break;
            }
        }

        // Sort
        $sort = $filters['sort'] ?? 'recent';
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'bookings':
                $query->orderBy('bookings_count', 'desc');
                break;
            case 'revenue':
                $query->orderBy('total_revenue', 'desc');
                break;
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    
    protected function getStatistics(): array
    {
        $totalGuests = Guest::count();
        $totalBookings = Booking::count();
        $repeatGuests = Guest::has('bookings', '>=', 2)->count();
        $totalRevenue = Order::where('status', 'paid')->sum('grand_total_inc_vat');

        return [
            'total_guests' => $totalGuests,
            'total_bookings' => $totalBookings,
            'repeat_guests' => $repeatGuests,
            'total_revenue' => $totalRevenue ?: 0, // Return raw number, let view format it
        ];
    }
}

