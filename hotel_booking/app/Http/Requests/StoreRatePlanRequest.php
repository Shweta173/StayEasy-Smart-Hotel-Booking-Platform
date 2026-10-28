<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatePlanRequest extends FormRequest
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
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'required|string|max:255',
            'price_per_night' => 'required|numeric|min:0|max:999999.99',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'min_nights' => 'nullable|integer|min:1',
            'max_nights' => 'nullable|integer|min:1|gte:min_nights',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'room_type_id.exists' => 'Selected room type does not exist',
            'price_per_night.required' => 'Price per night is required',
            'price_per_night.min' => 'Price must be greater than or equal to 0',
            'start_date.after_or_equal' => 'Start date must be today or later',
            'end_date.after' => 'End date must be after start date',
            'max_nights.gte' => 'Maximum nights must be greater than or equal to minimum nights',
        ];
    }
}
