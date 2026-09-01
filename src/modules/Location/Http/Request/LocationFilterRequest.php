<?php

namespace Modules\Location\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class LocationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_by' => ['string'],
            'order_direction' => ['string', 'in:asc,desc'],
            'limit' => ['numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_by.string' => __('location.validation.order_by_must_be_string'),

            'order_direction.string' => __('location.validation.order_direction_must_be_string'),
            'order_direction.in' => __('location.validation.order_direction_invalid'),

            'limit.numeric' => __('location.validation.limit_must_be_numeric'),
            'limit.min' => __('location.validation.limit_min_value'),
        ];
    }
}
