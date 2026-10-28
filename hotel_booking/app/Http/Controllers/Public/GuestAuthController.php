<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GuestAuthController extends Controller
{
    /**
     * Register a new guest account
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:guests',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $guest = Guest::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'country' => $request->country,
        ]);

        // Create Sanctum token
        $token = $guest->createToken('guest-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'guest' => [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'country' => $guest->country,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Login an existing guest
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $guest = Guest::where('email', $request->email)->first();

        if (!$guest || !$guest->password || !Hash::check($request->password, $guest->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Revoke all previous tokens
        $guest->tokens()->delete();

        // Create new Sanctum token
        $token = $guest->createToken('guest-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'guest' => [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'country' => $guest->country,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Logout the guest (revoke token)
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get the authenticated guest's profile
     */
    public function profile(Request $request)
    {
        $guest = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $guest->id,
                'first_name' => $guest->first_name,
                'last_name' => $guest->last_name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'country' => $guest->country,
            ]
        ]);
    }

    /**
     * Get all bookings for the authenticated guest
     */
    public function myBookings(Request $request)
    {
        $guest = $request->user();

        $bookings = $guest->bookings()
            ->with(['roomType.property.primaryImage', 'order.invoice'])
            ->orderBy('check_in', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                    'status' => $booking->status,
                    'guests' => $booking->num_guests ?? $booking->guests ?? 1,
                    'nights' => $booking->check_in->diffInDays($booking->check_out),
                    'total_amount' => $booking->order ? $booking->order->grand_total_inc_vat : 0,
                    'invoice_number' => $booking->order && $booking->order->invoice ? $booking->order->invoice->invoice_number : null,
                    'property' => [
                        'id' => $booking->roomType->property->id,
                        'name' => $booking->roomType->property->name,
                        'city' => $booking->roomType->property->city,
                        'state' => $booking->roomType->property->state,
                        'country' => $booking->roomType->property->country,
                        'image_url' => $booking->roomType->property->primaryImage->image_url ?? null,
                    ],
                    'room_type' => [
                        'id' => $booking->roomType->id,
                        'name' => $booking->roomType->name,
                    ],
                ];
            });

        // Separate into upcoming and past
        $now = now();
        $upcoming = $bookings->filter(function ($booking) use ($now) {
            return $booking['check_in'] >= $now->format('Y-m-d') && in_array($booking['status'], ['confirmed', 'pending', 'checked_in']);
        })->values();

        $past = $bookings->filter(function ($booking) use ($now) {
            return $booking['check_in'] < $now->format('Y-m-d') || in_array($booking['status'], ['checked_out', 'cancelled']);
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'guest' => [
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'email' => $guest->email,
                ],
                'upcoming' => $upcoming,
                'past' => $past,
                'total' => $bookings->count(),
            ]
        ]);
    }
}
