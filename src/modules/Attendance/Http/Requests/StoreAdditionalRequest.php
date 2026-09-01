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
            'is_system' => ['required', 'boolean', 'declined'],
            'is_additional' => ['required', 'boolean', 'accepted'],
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
            'is_system.required' => __('additional.validation.is_system_required'),
            'is_system.boolean' => __('additional.validation.is_system_must_be_boolean'),
            'is_system.declined' => __('additional.validation.system_service_cannot_create'),
            'is_additional.required' => __('additional.validation.is_additional_required'),
            'is_additional.boolean' => __('additional.validation.is_additional_must_be_boolean'),
            'is_additional.accepted' => __('additional.validation.service_type_invalid'),
        ];
    }
}
