<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'vat_registered',
        'vat_number',
        'default_vat_rate',
        'hotel_legal_name',
        'hotel_legal_address',
        'prices_include_vat',
    ];

    protected $casts = [
        'vat_registered' => 'boolean',
        'prices_include_vat' => 'boolean',
        'default_vat_rate' => 'decimal:2',
    ];

    /**
     * Get the single settings record (there should only be one)
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            // Create default settings if none exist
            $settings = self::create([]);
        }
        
        return $settings;
    }
}
