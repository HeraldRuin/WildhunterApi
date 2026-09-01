<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }
}
