<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display settings page
     */
    public function index(Request $request)
    {
        return $this->settingsService->index($request);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        return $this->settingsService->update($request);
    }
}
