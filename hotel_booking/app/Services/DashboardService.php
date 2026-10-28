<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Property;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $dashboard = $this->getDashboardData();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($dashboard, 'Dashboard data retrieved successfully');
        }

        // Return view for web requests - extract stats and recentBookings for backward compatibility
        $stats = [
            'total_properties' => $dashboard['counts']['total_properties'],
            'total_bookings' => $dashboard['counts']['total_bookings'],
            'confirmed_bookings' => $dashboard['counts']['confirmed_bookings'],
            'total_revenue' => $dashboard['revenue']['total_revenue'],
            'todays_checkins' => $dashboard['today']['check_ins'],
        ];
        
        // Get actual Eloquent models for recent bookings instead of array representation
        $recentBookings = Booking::with(['guest', 'roomType.property'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
  
    protected function getDashboardData(): array
    {
        $today = Carbon::today();
        
        // Today's check-ins and check-outs
        $todayCheckIns = Booking::where('check_in', $today)
            ->where('status', 'confirmed')
            ->count();
            
        $todayCheckOuts = Booking::where('check_out', $today)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        // Calculate occupancy percentage
        $totalUnits = RoomType::sum('total_units');
        $occupiedUnits = Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        // Revenue
        $totalRevenue = Order::where('status', 'paid')->sum('grand_total_inc_vat');
        $todayRevenue = Order::where('status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('grand_total_inc_vat');

        // Recent bookings
        $recentBookings = Booking::with(['guest', 'roomType.property'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'guest_name' => $booking->guest->full_name,
                    'property' => $booking->roomType->property->name,
                    'room_type' => $booking->roomType->name,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Unpaid invoices
        $unpaidInvoices = Invoice::where('status', 'issued')
            ->with(['order.booking.guest'])
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->order->grand_total_inc_vat,
                    'guest_name' => $invoice->order->booking->guest->full_name ?? 'N/A',
                    'issued_at' => $invoice->issued_at?->format('Y-m-d'),
                    'due_at' => $invoice->due_at?->format('Y-m-d'),
                ];
            });

        return [
            'today' => [
                'check_ins' => $todayCheckIns,
                'check_outs' => $todayCheckOuts,
                'revenue' => number_format($todayRevenue, 2),
                'todays_checkins' => $todayCheckIns,
            ],
            'occupancy' => [
                'rate' => $occupancyRate,
                'occupied_units' => $occupiedUnits,
                'total_units' => $totalUnits,
            ],
            'revenue' => [
                'total' => number_format($totalRevenue, 2),
                'today' => number_format($todayRevenue, 2),
                'total_revenue' => $totalRevenue,
            ],
            'counts' => [
                'properties' => Property::count(),
                'published_properties' => Property::where('status', 'published')->count(),
                'total_properties' => Property::count(),
                'total_bookings' => Booking::count(),
                'active_bookings' => Booking::whereIn('status', ['confirmed', 'checked_in'])->count(),
                'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            ],
            'recent_bookings' => $recentBookings,
            'unpaid_invoices' => $unpaidInvoices,
        ];
    }

    public function getStats(array $dateRange = []): array
    {
        $startDate = $dateRange['start'] ?? Carbon::now()->subDays(30);
        $endDate = $dateRange['end'] ?? Carbon::now();

        return [
            'overview' => $this->getOverviewStats(),
            'bookings' => $this->getBookingStats($startDate, $endDate),
            'revenue' => $this->getRevenueStats($startDate, $endDate),
            'occupancy' => $this->getOccupancyStats($startDate, $endDate),
            'recent_activity' => $this->getRecentActivity(),
        ];
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(): array
    {
        return [
            'total_properties' => Property::count(),
            'active_properties' => Property::where('is_active', true)->count(),
            'total_guests' => Guest::count(),
            'total_bookings' => Booking::count(),
        ];
    }

    private function getBookingStats($startDate, $endDate): array
    {
        $baseQuery = Booking::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => (clone $baseQuery)->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'checked_in' => (clone $baseQuery)->where('status', 'checked_in')->count(),
            'checked_out' => (clone $baseQuery)->where('status', 'checked_out')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'by_day' => $this->getBookingsByDay($startDate, $endDate),
        ];
    }

    private function getRevenueStats($startDate, $endDate): array
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->get();

        $payments = Payment::where('type', 'charge')
            ->where('status', 'succeeded')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_revenue' => $orders->sum('grand_total_inc_vat'),
            'total_payments' => $payments->sum('amount'),
            'average_order_value' => $orders->avg('grand_total_inc_vat') ?? 0,
            'total_orders' => $orders->count(),
            'by_day' => $this->getRevenueByDay($startDate, $endDate),
        ];
    }

    private function getOccupancyStats($startDate, $endDate): array
    {
        $totalNights = Carbon::parse($startDate)->diffInDays($endDate);
        
        $bookedNights = Booking::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in', [$startDate, $endDate])
                  ->orWhereBetween('check_out', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('check_in', '<=', $startDate)
                         ->where('check_out', '>=', $endDate);
                  });
            })
            ->get()
            ->sum(function ($booking) use ($startDate, $endDate) {
                $checkIn = max(Carbon::parse($booking->check_in), Carbon::parse($startDate));
                $checkOut = min(Carbon::parse($booking->check_out), Carbon::parse($endDate));
                return $checkIn->diffInDays($checkOut);
            });

        $totalRoomNights = DB::table('room_types')->sum('total_units') * $totalNights;
        $occupancyRate = $totalRoomNights > 0 ? ($bookedNights / $totalRoomNights) * 100 : 0;

        return [
            'occupancy_rate' => round($occupancyRate, 2),
            'booked_nights' => $bookedNights,
            'total_room_nights' => $totalRoomNights,
        ];
    }

    private function getRecentActivity(): array
    {
        return [
            'recent_bookings' => Booking::with(['guest', 'roomType.property'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'today_check_ins' => Booking::where('check_in', Carbon::today())
                ->where('status', 'confirmed')
                ->with(['guest', 'roomType.property'])
                ->get(),
            'today_check_outs' => Booking::where('check_out', Carbon::today())
                ->where('status', 'checked_in')
                ->with(['guest', 'roomType.property'])
                ->get(),
        ];
    }
 
    private function getBookingsByDay($startDate, $endDate): array
    {
        $bookings = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return $bookings;
    }

    private function getRevenueByDay($startDate, $endDate): array
    {
        $revenue = Order::selectRaw('DATE(created_at) as date, SUM(grand_total_inc_vat) as total')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        return $revenue;
    }
}
