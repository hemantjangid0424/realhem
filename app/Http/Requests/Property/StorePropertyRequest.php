<?php

namespace App\Http\Requests\Property;

use App\Enums\PropertyFor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
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
            // Step 1: Basic & Location Details
            'user_type' => ['nullable', 'string', 'in:Owner,Agent,Builder'],
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

            // Step 2: Property Profile & Layout
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:10'],
            'carpet_area' => ['required', 'integer', 'min:10'],
            'super_builtup_area' => ['nullable', 'integer'],
            'furnishing_status' => ['nullable', 'string'],
            'floor_no' => ['nullable', 'string', 'max:50'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:150'],
            'facing' => ['nullable', 'string', 'max:50'],
            'construction_status' => ['nullable', 'string', 'max:100'],

            // Step 3: Photos, Videos & Amenities
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:100'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['string', 'max:512'],
            'videos' => ['nullable', 'array', 'max:5'],
            'videos.*' => ['string', 'max:512'],

            // Step 4: Pricing & Finalize
            'expected_price' => ['required', 'numeric', 'min:1000'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'price_negotiable' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
