<?php

namespace App\Services;

use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsService
{
    use ApiResponse;

    
    public function index(Request $request)
    {
        $settings = Setting::getSettings();

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->successResponse($settings, 'Settings retrieved successfully');
        }

        // Return view for web requests
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'site_name' => 'nullable|string|max:255',
                'site_email' => 'nullable|email|max:255',
                'site_phone' => 'nullable|string|max:20',
                'currency' => 'nullable|string|max:3',
                'currency_symbol' => 'nullable|string|max:5',
                'default_vat_rate' => 'nullable|numeric|min:0|max:100',
                'vat_number' => 'nullable|string|max:30',
                'hotel_legal_name' => 'nullable|string|max:200',
                'hotel_legal_address' => 'nullable|string',
                'stripe_publishable_key' => 'nullable|string',
                'stripe_secret_key' => 'nullable|string',
                'stripe_webhook_secret' => 'nullable|string',
                'booking_hold_duration' => 'nullable|integer|min:5|max:60',
                'cancellation_policy' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'privacy_policy' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                // Always return JSON for AJAX requests (detected by X-Requested-With header)
                if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                throw new ValidationException($validator);
            }

            $settings = $this->updateSettings($request->all());

            // Return JSON for API/AJAX requests
            if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully',
                    'data' => $settings
                ]);
            }

            // Return redirect for web requests
            return redirect()->route('admin.settings')
                ->with('success', 'Settings updated successfully');

        } catch (ValidationException $e) {
            if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Settings update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    protected function updateSettings(array $data): Setting
    {
        $settings = Setting::getSettings();

        $fillableFields = [
            'site_name',
            'site_email',
            'site_phone',
            'currency',
            'currency_symbol',
            'default_vat_rate',
            'vat_number',
            'vat_registered',
            'prices_include_vat',
            'hotel_legal_name',
            'hotel_legal_address',
            'stripe_publishable_key',
            'stripe_secret_key',
            'stripe_webhook_secret',
            'booking_hold_duration',
            'cancellation_policy',
            'terms_and_conditions',
            'privacy_policy',
        ];

        foreach ($fillableFields as $field) {
            if (array_key_exists($field, $data)) {
                $settings->$field = $data[$field];
            }
        }

        // Handle boolean fields (checkboxes) - they're not sent if unchecked
        // Note: Frontend sends '0' for unchecked, '1' for checked
        $settings->vat_registered = isset($data['vat_registered']) && $data['vat_registered'] !== '0' ? true : false;
        $settings->prices_include_vat = isset($data['prices_include_vat']) && $data['prices_include_vat'] !== '0' ? true : false;

        $settings->save();

        return $settings->fresh();
    }
}
