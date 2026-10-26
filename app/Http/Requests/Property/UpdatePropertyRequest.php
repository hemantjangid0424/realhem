<?php

namespace App\Http\Requests\Property;

use App\Enums\PropertyFor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'property_for' => ['required', Rule::enum(PropertyFor::class)],
            'property_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'locality' => ['required', 'string', 'max:150'],
            'sub_locality' => ['nullable', 'string', 'max:150'],
            'project_name' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:10'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['nullable', 'string'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer'],
            'facing' => ['nullable', 'string'],
            'construction_status' => ['nullable', 'string'],
            'expected_price' => ['required', 'numeric', 'min:1000'],
            'maintenance_charge' => ['nullable', 'numeric'],
            'price_negotiable' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'videos' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:active,pending_approval,sold,rented'],
        ];
    }
}
