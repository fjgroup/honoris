<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Policies on controller methods will enforce this
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $contractId = $this->route('shop_contract') ? $this->route('shop_contract')->id : null;

        return [
            'map_plot_id' => 'sometimes|required|exists:map_plots,id',
            'owner_id' => 'sometimes|required|exists:owners,id',
            'building_type_id' => 'sometimes|required|exists:building_types,id',
            'assigned_to_user_id' => 'nullable|sometimes|exists:users,id',
            'shop_request_id' => [
                'nullable',
                'sometimes',
                'exists:shop_requests,id',
                Rule::unique('shop_contracts', 'shop_request_id')->ignore($contractId),
            ],
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => 'sometimes|required|string|in:active,inactive,expired,pending_renewal',
        ];
    }
}
