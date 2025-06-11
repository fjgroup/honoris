<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User; // Import User model

class StoreOwnerRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id|unique:owners,user_id',
            'name' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'language_preference' => 'required|in:en,es',
            'is_for_all_users' => 'required|boolean',
            'description' => 'nullable|string',
        ];
    }
}
