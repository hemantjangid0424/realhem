<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
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
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image file to upload as your profile picture.',
            'avatar.image' => 'The selected file must be a valid image.',
            'avatar.mimes' => 'Profile picture must be a file of type: JPEG, PNG, JPG, or WebP.',
            'avatar.max' => 'Profile picture size must not exceed 5MB.',
        ];
    }
}
