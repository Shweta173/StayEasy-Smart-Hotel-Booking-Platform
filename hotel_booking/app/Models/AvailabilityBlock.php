<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'start_date',
        'end_date',
        'reason',
        'blocked_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the room type that owns this block
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the admin user who created this block
     */
    public function blockedBy()
    {
        return $this->belongsTo(AdminUser::class, 'blocked_by');
    }
}
