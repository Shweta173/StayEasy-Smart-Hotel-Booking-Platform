<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookingHold extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'check_in',
        'check_out',
        'guest_id',
        'num_rooms',
        'num_guests',
        'status',
        'expires_at',
        'payment_intent_id',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'expires_at' => 'datetime',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<=', Carbon::now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helpers
    public function isExpired()
    {
        return $this->status === 'pending' && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return in_array($this->status, ['pending', 'confirmed']) && !$this->isExpired();
    }
}
