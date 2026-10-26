<?php

namespace App\Http\Requests\Activity;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncUserActivitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activities' => ['required', 'array', 'max:100'],
            'activities.*.property_id' => ['nullable', 'integer'],
            'activities.*.activity_type' => ['nullable', 'string', 'max:50'],
            'activities.*.metadata' => ['nullable', 'array'],
            'activities.*.created_at' => ['nullable', 'string'],
        ];
    }
}
