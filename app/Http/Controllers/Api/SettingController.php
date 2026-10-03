<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get public branding and platform settings.
     */
    public function publicIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => Setting::getAll(),
        ]);
    }

    /**
     * Get admin branding and platform settings.
     */
    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => Setting::getAll(),
        ]);
    }

    /**
     * Update branding and company settings (Admin only).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:100',
            'company_short_name' => 'required|string|max:10',
            'company_tagline' => 'required|string|max:255',
            'company_sub_tagline' => 'nullable|string|max:255',
            'company_logo_url' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:100',
            'brand_primary_color' => 'nullable|string|max:20',
            'brand_secondary_color' => 'nullable|string|max:20',
            'brand_accent_color' => 'nullable|string|max:20',
            'theme_font_family' => 'nullable|string|max:50',
            'theme_border_radius' => 'nullable|string|max:20',
            'header_style' => 'nullable|string|max:50',
            'hero_style' => 'nullable|string|max:50',
            'banner_enabled' => 'nullable|string|max:10',
            'banner_text' => 'nullable|string|max:500',
            'banner_badge' => 'nullable|string|max:50',
            'banner_link' => 'nullable|string|max:255',
            'footer_style' => 'nullable|string|max:50',
            'footer_about' => 'nullable|string|max:1000',
            'copyright_text' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            $group = match ($key) {
                'brand_primary_color', 'brand_secondary_color', 'brand_accent_color', 'theme_font_family', 'theme_border_radius', 'header_style', 'hero_style' => 'theme',
                'banner_enabled', 'banner_text', 'banner_badge', 'banner_link' => 'banner',
                'company_phone', 'company_email' => 'contact',
                'footer_about', 'copyright_text', 'footer_style' => 'footer',
                default => 'branding',
            };

            Setting::set($key, (string) $value, $group);
        }

        return response()->json([
            'success' => true,
            'message' => 'Company branding & settings updated successfully!',
            'settings' => Setting::getAll(),
        ]);
    }
}
