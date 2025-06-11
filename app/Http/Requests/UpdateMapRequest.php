<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Ensure Rule is imported

class UpdateMapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city_id' => 'sometimes|required|exists:cities,id',
            'name' => 'sometimes|required|string|max:255',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:4096', // Allows nullable for optional update
            'description' => 'nullable|string',
        ];
    }
}
