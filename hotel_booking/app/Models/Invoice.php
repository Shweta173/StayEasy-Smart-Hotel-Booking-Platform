<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'status',
        'issued_at',
        'due_at',
        'pdf_url',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Helpers
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isIssued()
    {
        return $this->status === 'issued';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isVoid()
    {
        return $this->status === 'void';
    }
}
