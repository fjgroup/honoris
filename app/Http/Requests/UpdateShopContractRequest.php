<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\PreventPlotDateOverlap; // Import the custom rule

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
        // $this->shopContract is available if route model binding is used for 'shop_contract'
        $currentShopContract = $this->route('shop_contract');


        return [
            'map_plot_id' => [
                'sometimes',
                'required',
                'exists:map_plots,id',
                function ($attribute, $value, $fail) use ($currentShopContract) {
                    // If map_plot_id is not being changed, $value is the current plot_id.
                    // If it is being changed, $value is the new plot_id.
                    $mapPlotIdToValidate = $value;

                    // If start_date is in request, use it, else use existing.
                    $startDate = $this->input('start_date', $currentShopContract->start_date);
                    // If end_date is in request, use it, else use existing.
                    $endDate = $this->input('end_date', $currentShopContract->end_date);

                    $currentContractIdBeingUpdated = $currentShopContract->id;

                    // Only apply the rule if dates are present and potentially valid
                    if ($mapPlotIdToValidate && $startDate && $endDate) {
                        $rule = new PreventPlotDateOverlap($mapPlotIdToValidate, $startDate, $endDate, $currentContractIdBeingUpdated);
                        if (!$rule->passes($attribute, $mapPlotIdToValidate)) {
                            $fail($rule->message());
                        }
                    }
                }
            ],
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
