<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'guest_id',
        'booking_id',
        'guest_name',
        'guest_email',
        'rating',
        'status',
        'comment',
        'staff_reply',
        'replied_at',
        'replied_by',
        'moderated_by',
        'moderated_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function moderator()
    {
        return $this->belongsTo(AdminUser::class, 'moderated_by');
    }

    // Helpers
    public function hasReply()
    {
        return !empty($this->staff_reply);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
