<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_id' => ['required', 'integer'],
            'count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'food_id.required' => __('booking.validation.food_id_required'),
            'food_id.integer' => __('booking.validation.food_id_must_be_integer'),
            'count.required' => __('booking.validation.service_count_required'),
            'count.integer' => __('booking.validation.service_count_must_be_integer'),
            'count.min' => __('booking.validation.service_count_min_value'),
        ];
    }
}
