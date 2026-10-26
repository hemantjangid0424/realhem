<?php

namespace App\Http\Requests\Property;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadPropertyVideosRequest extends FormRequest
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
            'videos' => ['required', 'array', 'min:1', 'max:3'],
            'videos.*' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v', 'max:51200'],
        ];
    }
}
