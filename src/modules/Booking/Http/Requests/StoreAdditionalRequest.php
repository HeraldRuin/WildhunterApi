<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdditionalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'additional_id' => ['required', 'integer', 'exists:bc_addetional_prices,id'],
            'count' => ['required', 'integer', 'min:1'],
            'hunter_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('booking.validation.additional_name_required'),
            'name.string' => __('booking.validation.additional_name_must_be_string'),
            'additional_id.required' => __('booking.validation.additional_id_required'),
            'additional_id.integer' => __('booking.validation.additional_id_must_be_integer'),
            'additional_id.exists' => __('booking.validation.additional_id_not_found'),
            'count.required' => __('booking.validation.service_count_required'),
            'count.integer' => __('booking.validation.service_count_must_be_integer'),
            'count.min' => __('booking.validation.service_count_min_value'),
            'hunter_id.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_id.exists' => __('booking.validation.hunter_id_not_found'),
        ];
    }
}
