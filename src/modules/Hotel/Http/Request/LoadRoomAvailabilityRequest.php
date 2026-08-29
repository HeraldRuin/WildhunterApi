<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class LoadRoomAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required'],
            'start' => ['required', 'date', 'date_format:Y-m-d'],
            'end' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start'],
            'for_single' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => __('hotel.validation.calendar_id_required'),
            'start.required' => __('hotel.validation.start_required'),
            'start.date' => __('hotel.validation.start_must_be_date'),
            'start.date_format' => __('hotel.validation.start_must_be_date'),
            'end.required' => __('hotel.validation.end_required'),
            'end.date' => __('hotel.validation.end_must_be_date'),
            'end.date_format' => __('hotel.validation.end_must_be_date'),
            'end.after_or_equal' => __('hotel.validation.end_after_or_equal_start'),
        ];
    }
}
