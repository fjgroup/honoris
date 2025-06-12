<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PreventPlotDateOverlap; // Import the custom rule

class StoreShopContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Assuming contract management is admin/captain only,
        // policies on the controller methods will enforce this.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'map_plot_id' => [
                'required',
                'exists:map_plots,id',
                function ($attribute, $value, $fail) {
                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');
                    // Only apply the rule if dates are present and potentially valid (basic format)
                    if ($startDate && $endDate) {
                        $rule = new PreventPlotDateOverlap($value, $startDate, $endDate);
                        if (!$rule->passes($attribute, $value)) {
                            $fail($rule->message());
                        }
                    }
                }
            ],
            'owner_id' => 'required|exists:owners,id',
            'building_type_id' => 'required|exists:building_types,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'shop_request_id' => 'nullable|exists:shop_requests,id|unique:shop_contracts,shop_request_id', // A request should only be fulfilled by one contract
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,inactive,expired,pending_renewal', // Example statuses
            // Consider adding 'rent_amount', 'payment_schedule', etc. in a real scenario
        ];
    }
}
