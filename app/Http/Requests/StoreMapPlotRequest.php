<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMapPlotRequest extends FormRequest
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
            'map_id' => 'required|exists:maps,id',
            'plot_identifier' => 'required|string|max:255',
            'coord_x' => 'required|integer|min:0',
            'coord_y' => 'required|integer|min:0',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean', // Default will be handled in controller
            'notes' => 'nullable|string',
        ];
    }
}
