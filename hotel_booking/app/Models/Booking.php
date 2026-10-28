<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'room_type_id',
        'check_in',
        'check_out',
        'guest_id',
        'booking_hold_id',
        'status',
        'num_guests',
        'special_requests',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'refund_amount',
        'refund_percentage',
        'refund_status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK-' . strtoupper(Str::random(10));
            }
        });
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function property()
    {
        return $this->hasOneThrough(Property::class, RoomType::class, 'id', 'id', 'room_type_id', 'property_id');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function bookingHold()
    {
        return $this->belongsTo(BookingHold::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Helpers
    public function getNumNightsAttribute()
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    public function canCheckIn()
    {
        // Check-in is only allowed on the actual check-in date
        $today = now()->toDateString();
        $checkInDate = Carbon::parse($this->check_in)->toDateString();
        
        // Must be confirmed status AND must be the check-in date
        return $this->status === 'confirmed' && $today === $checkInDate;
    }

    public function canCheckOut()
    {
        return $this->status === 'checked_in';
    }
}
