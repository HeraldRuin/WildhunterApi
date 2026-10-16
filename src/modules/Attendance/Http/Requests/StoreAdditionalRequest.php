<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Models\AddetionalPrice;

class StoreAdditionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'count' => ['required', 'integer', 'min:0'],
            'calculation_type' => [
                'required',
                'string',
                Rule::in([AddetionalPrice::INDIVIDUAL, AddetionalPrice::PERSON]),
            ],
            'is_system' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('additional.validation.name_required'),
            'name.string' => __('additional.validation.name_must_be_string'),
            'name.max' => __('additional.validation.name_max'),
            'price.required' => __('additional.validation.price_required'),
            'price.numeric' => __('additional.validation.price_must_be_numeric'),
            'price.min' => __('additional.validation.price_min'),
            'count.required' => __('additional.validation.count_required'),
            'count.integer' => __('additional.validation.count_must_be_integer'),
            'count.min' => __('additional.validation.count_min'),
            'calculation_type.required' => __('additional.validation.calculation_type_required'),
            'calculation_type.in' => __('additional.validation.calculation_type_invalid'),
            'is_system.required' => __('additional.validation.is_system_required'),
            'is_system.boolean' => __('additional.validation.is_system_must_be_boolean'),
        ];
    }
}
