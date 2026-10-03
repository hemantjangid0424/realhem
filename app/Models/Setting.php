<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Default fallback branding settings.
     */
    public const DEFAULTS = [
        'company_name' => 'RealHem',
        'company_short_name' => 'RH',
        'company_tagline' => '99acres Real Estate Architecture & Portal',
        'company_sub_tagline' => 'Buy, Rent, PG, Commercial & Builder Projects',
        'company_logo_url' => '',
        'company_phone' => '1800-41-9999',
        'company_email' => 'contact@realhem.com',
        'brand_primary_color' => '#005ca8',
        'brand_secondary_color' => '#1e40af',
        'brand_accent_color' => '#ff6b35',
        'theme_font_family' => 'Instrument Sans',
        'theme_border_radius' => '16px',
        'header_style' => 'clean_white',
        'hero_style' => 'midnight_blue',
        'banner_enabled' => 'true',
        'banner_text' => "India's Biggest Property Fest 2026: Zero Brokerage on 10,000+ Verified Homes",
        'banner_badge' => 'MEGA LAUNCH',
        'banner_link' => '/listings?badge=fest',
        'footer_style' => 'dark_slate',
        'footer_about' => 'RealHem is India\'s premier full-stack real estate discovery and property transaction portal, connecting verified property owners, top developers, and licensed brokers with over 5 Lakh+ buyers and tenants every week.',
        'copyright_text' => '© 2026 RealHem India Pvt. Ltd. All rights reserved.',
    ];

    /**
     * Get a setting value by key with fallback default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : ($default ?? (self::DEFAULTS[$key] ?? null));
    }

    /**
     * Set/update a setting value.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        Cache::forget('app_branding_settings');

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all settings merged with defaults.
     */
    public static function getAll(): array
    {
        return Cache::remember('app_branding_settings', 3600, function () {
            $dbSettings = static::pluck('value', 'key')->toArray();

            return array_merge(self::DEFAULTS, $dbSettings);
        });
    }
}
