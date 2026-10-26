<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:100'],
            'company_short_name' => ['required', 'string', 'max:10'],
            'company_tagline' => ['required', 'string', 'max:255'],
            'company_sub_tagline' => ['nullable', 'string', 'max:255'],
            'company_logo_url' => ['nullable', 'string', 'max:500'],
            'company_favicon_url' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'brand_primary_color' => ['nullable', 'string', 'max:20'],
            'brand_secondary_color' => ['nullable', 'string', 'max:20'],
            'brand_accent_color' => ['nullable', 'string', 'max:20'],
            'theme_font_family' => ['nullable', 'string', 'max:50'],
            'theme_border_radius' => ['nullable', 'string', 'max:20'],
            'header_style' => ['nullable', 'string', 'max:50'],
            'hero_style' => ['nullable', 'string', 'max:50'],
            'festival_animation' => ['nullable', 'string', 'max:50'],
            'banner_enabled' => ['nullable', 'string', 'max:10'],
            'banner_text' => ['nullable', 'string', 'max:500'],
            'banner_badge' => ['nullable', 'string', 'max:50'],
            'banner_link' => ['nullable', 'string', 'max:255'],
            'footer_style' => ['nullable', 'string', 'max:50'],
            'footer_about' => ['nullable', 'string', 'max:1000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
