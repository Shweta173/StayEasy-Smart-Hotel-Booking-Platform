<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the room type that is blocked
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the admin user who created the block
     */
    public function blockedBy()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Check if a date range overlaps with this block
     */
    public function overlaps($startDate, $endDate)
    {
        return $this->start_date < $endDate && $this->end_date > $startDate;
    }

    /**
     * Get blocks for a specific room type and date range
     */
    public static function getBlocksForDateRange($roomTypeId, $startDate, $endDate)
    {
        return self::where('room_type_id', $roomTypeId)
            ->where('status', 'active')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    // Block starts before or during the range and ends during or after
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>', $startDate);
                });
            })
            ->get();
    }

    /**
     * Count blocked units for a specific date
     */
    public static function countBlockedUnitsForDate($roomTypeId, $date)
    {
        return self::where('room_type_id', $roomTypeId)
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>', $date)
            ->count();
    }
}
