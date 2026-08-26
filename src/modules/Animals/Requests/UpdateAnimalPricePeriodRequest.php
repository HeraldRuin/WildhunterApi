<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimalPricePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => __('animal.validation.start_date_required'),
            'start_date.date' => __('animal.validation.start_date_must_be_date'),
            'start_date.date_format' => __('animal.validation.start_date_must_be_date'),
            'end_date.required' => __('animal.validation.end_date_required'),
            'end_date.date' => __('animal.validation.end_date_must_be_date'),
            'end_date.date_format' => __('animal.validation.end_date_must_be_date'),
            'end_date.after_or_equal' => __('animal.validation.end_date_after_or_equal'),
            'amount.required' => __('animal.validation.amount_required'),
            'amount.numeric' => __('animal.validation.amount_must_be_numeric'),
            'amount.min' => __('animal.validation.amount_min'),
        ];
    }
}
