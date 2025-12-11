<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings.
     */
    public function index()
    {
        // Return settings as a key-value pair object
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'whatsapp_support_number' => 'nullable|string',
            'watermark_text' => 'nullable|string',
            'watermark_opacity' => 'nullable|numeric|min:0|max:100',
            'maintenance_mode' => 'boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
