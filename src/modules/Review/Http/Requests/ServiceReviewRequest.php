<?php

namespace Modules\Review\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255'],

            'order_by' => ['nullable', 'string'],
            'order_direction' => ['nullable', 'in:asc,desc'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('review.validation.type_required'),
            'type.string' => __('review.validation.type_string'),

            'order_by.string' => __('review.validation.order_by_must_be_string'),

            'order_direction.string' => __('review.validation.order_direction_must_be_string'),
            'order_direction.in' => __('review.validation.order_direction_invalid'),

            'limit.numeric' => __('review.validation.limit_must_be_numeric'),
            'limit.min' => __('review.validation.limit_min_value'),
        ];
    }
}
