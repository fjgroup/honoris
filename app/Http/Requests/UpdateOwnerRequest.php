<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User; // Import User model

class UpdateOwnerRequest extends FormRequest
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
        // $ownerId = $this->route('owner') ? $this->route('owner')->id : null;
        // The line above is problematic because $this->route('owner') might be an ID (string/int) or an Owner model instance
        // depending on how route model binding is configured and how the route is called.
        // A safer way is to access the id directly from the owner model instance if it's resolved.
        $owner = $this->route('owner');
        $ownerId = $owner instanceof \App\Models\Owner ? $owner->id : ($owner ? $owner : null);


        return [
            'user_id' => ['sometimes', 'required', 'exists:users,id', Rule::unique('owners', 'user_id')->ignore($ownerId)],
            'name' => 'sometimes|required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'language_preference' => 'sometimes|required|in:en,es',
            'is_for_all_users' => 'sometimes|required|boolean',
            'description' => 'nullable|string',
        ];
    }
}
