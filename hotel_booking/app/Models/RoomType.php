<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'description',
        'base_price',
        'max_occupancy',
        'total_units',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities');
    }

    public function ratePlans()
    {
        return $this->hasMany(RatePlan::class);
    }

    public function bookingHolds()
    {
        return $this->hasMany(BookingHold::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function blockedDates()
    {
        return $this->hasMany(BlockedDate::class);
    }

    /**
     * Get the price for a specific date considering rate plans
     */
    public function getPriceForDate($date)
    {
        $ratePlan = $this->ratePlans()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->orderBy('price_per_night', 'desc') // Get highest rate if multiple apply
            ->first();

        $price = $ratePlan ? $ratePlan->price_per_night : $this->base_price;

        return $price;
    }
}
