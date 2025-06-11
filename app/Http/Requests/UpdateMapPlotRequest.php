<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapPlotRequest extends FormRequest
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
            'map_id' => 'sometimes|required|exists:maps,id',
            'plot_identifier' => 'sometimes|required|string|max:255',
            'coord_x' => 'sometimes|required|integer|min:0',
            'coord_y' => 'sometimes|required|integer|min:0',
            'width' => 'sometimes|required|integer|min:1',
            'height' => 'sometimes|required|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
