<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateLegalSettingsRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Requests\Setting\UploadAssetRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        ])->header('Cache-Control', 'public, max-age=600');
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
     * Upload branding asset (Logo or Favicon).
     */
    public function uploadAsset(UploadAssetRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $prefix = $request->input('type', 'brand');
        $filename = $prefix.'_'.Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('branding', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::url($path),
            'filename' => $filename,
            'message' => ucfirst((string) $prefix).' uploaded successfully!',
        ]);
    }

    /**
     * Update branding and company settings (Admin only).
     */
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            $group = match ($key) {
                'brand_primary_color', 'brand_secondary_color', 'brand_accent_color', 'theme_font_family', 'theme_border_radius', 'header_style', 'hero_style', 'festival_animation' => 'theme',
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

    /**
     * Get legal pages and statutory policy texts.
     */
    public function getLegalSettings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'legal' => [
                'terms' => Setting::get('legal_terms', ''),
                'privacy' => Setting::get('legal_privacy', ''),
                'rera' => Setting::get('legal_rera', ''),
                'refund' => Setting::get('legal_refund', ''),
                'updated_at' => Setting::get('legal_updated_at', 'October 2026'),
                'version' => Setting::get('legal_version', '2.4'),
            ],
        ])->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Update legal pages and statutory policy texts (Admin only).
     */
    public function updateLegalSettings(UpdateLegalSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Setting::set("legal_{$key}", (string) ($value ?? ''), 'legal');
        }

        return response()->json([
            'success' => true,
            'message' => 'Legal documents and policies updated successfully!',
            'legal' => [
                'terms' => Setting::get('legal_terms', ''),
                'privacy' => Setting::get('legal_privacy', ''),
                'rera' => Setting::get('legal_rera', ''),
                'refund' => Setting::get('legal_refund', ''),
                'updated_at' => Setting::get('legal_updated_at', 'October 2026'),
                'version' => Setting::get('legal_version', '2.4'),
            ],
        ]);
    }
}
