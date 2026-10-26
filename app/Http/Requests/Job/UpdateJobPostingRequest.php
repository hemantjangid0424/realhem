<?php

namespace App\Http\Requests\Job;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'department' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:50'],
            'experience' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'description' => ['required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];
    }
}
