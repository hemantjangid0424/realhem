<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLegalSettingsRequest extends FormRequest
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
            'terms' => ['nullable', 'string'],
            'privacy' => ['nullable', 'string'],
            'rera' => ['nullable', 'string'],
            'refund' => ['nullable', 'string'],
            'updated_at' => ['nullable', 'string', 'max:50'],
            'version' => ['nullable', 'string', 'max:20'],
        ];
    }
}
