<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy will handle specific user rights, esp. for admin/captain updates
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|required|in:pending,approved,rejected,fulfilled,cancelled',
            'assigned_contract_id' => 'nullable|sometimes|exists:shop_contracts,id',
            // Admins might also be allowed to correct original request details:
            'city_id' => 'sometimes|required|exists:cities,id',
            'building_type_id' => 'sometimes|required|exists:building_types,id',
            'notes' => 'nullable|string|max:1000', // Max length consistent with store
        ];
    }
}
