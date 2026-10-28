<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'guest_id',
        'order_number',
        'subtotal_ex_vat',
        'tax_rate',
        'tax_amount',
        'vat_total',
        'service_fee',
        'processing_fee',
        'grand_total_inc_vat',
        'currency',
        'payment_method',
        'stripe_payment_intent_id',
        'status',
    ];

    protected $casts = [
        'subtotal_ex_vat' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'grand_total_inc_vat' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for items() relationship for backwards compatibility
     */
    public function orderItems()
    {
        return $this->items();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helpers
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isRefunded()
    {
        return $this->status === 'refunded';
    }
}
