<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;

/**
 * Public Settings API
 * 
 * Provides access to settings needed for frontend display logic,
 * particularly VAT/tax settings for pricing display.
 */
class SettingsController extends Controller
{
    public function index()
    {
        try {
            $settings = Setting::getSettings();
            
            return response()->json([
                'success' => true,
                'settings' => [
                    'vat_registered' => $settings->vat_registered,
                    'default_vat_rate' => (float) $settings->default_vat_rate,
                    'prices_include_vat' => $settings->prices_include_vat,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
