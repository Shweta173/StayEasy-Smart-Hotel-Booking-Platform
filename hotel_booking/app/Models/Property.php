<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'city',
        'state',
        'country',
        'address',
        'postal_code',
        'status',
        'created_by',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('properties.status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('properties.status', 'draft');
    }

    // Accessors
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('status', 'approved')->avg('rating');
    }

    public function getMinPriceAttribute()
    {
        return $this->roomTypes()->min('base_price');
    }
}
