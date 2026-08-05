<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'exists:bc_hotels,id'],
            'check_in' => ['required', 'date', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'hunter_data' => ['nullable', 'date', 'date_format:Y-m-d'],
            'hunters' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'hotel_id.required' => __('hotel.validation.hotel_id_required'),
            'hotel_id.integer' => __('hotel.validation.hotel_id_must_be_integer'),
            'hotel_id.exists' => __('hotel.errors.hotel_not_found'),

            'check_in.required' => __('hotel.validation.check_in_required'),
            'check_in.date' => __('hotel.validation.check_in_must_be_date'),
            'check_in.date_format' => __('hotel.validation.check_in_must_be_date'),

            'check_out.required' => __('hotel.validation.check_out_required'),
            'check_out.date' => __('hotel.validation.check_out_must_be_date'),
            'check_out.date_format' => __('hotel.validation.check_out_must_be_date'),
            'check_out.after' => __('hotel.validation.check_out_must_be_after_check_in'),

            'adults.required' => __('hotel.validation.adults_required'),
            'adults.integer' => __('hotel.validation.adults_must_be_integer'),
            'adults.min' => __('hotel.validation.adults_min_value'),

            'hunter_data.date' => __('hotel.validation.hunter_data_must_be_date'),
            'hunter_data.date_format' => __('hotel.validation.hunter_data_must_be_date'),

            'hunters.integer' => __('hotel.validation.hunters_must_be_integer'),
            'hunters.min' => __('hotel.validation.hunters_min_value'),
        ];
    }
}
