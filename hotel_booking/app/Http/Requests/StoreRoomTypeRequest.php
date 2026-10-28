<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'total_units' => 'required|integer|min:1|max:1000',
            'size_sqm' => 'nullable|numeric|min:0',
            'bed_type' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'property_id.exists' => 'Selected property does not exist',
            'total_units.required' => 'Number of units is required',
            'total_units.min' => 'Must have at least 1 unit',
            'amenities.*.exists' => 'One or more selected amenities are invalid',
        ];
    }
}
