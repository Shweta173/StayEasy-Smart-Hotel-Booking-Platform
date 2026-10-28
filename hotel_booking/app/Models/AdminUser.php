<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'created_by');
    }

    // Role checks
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isFrontDesk()
    {
        return $this->role === 'front_desk';
    }

    public function canManageProperties()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function canManagePricing()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function canManageBookings()
    {
        return true; // All roles can view/manage bookings
    }
}
