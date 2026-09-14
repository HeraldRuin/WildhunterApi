<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHuntersCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hunters_count' => ['required', 'integer', 'min:1'],
            'max_hunters_count' => ['required', 'integer', 'min:1', 'gte:hunters_count'],
        ];
    }

    public function messages(): array
    {
        return [
            'hunters_count.required' => __('animal.validation.hunters_count_required'),
            'hunters_count.integer' => __('animal.validation.hunters_count_must_be_integer'),
            'hunters_count.min' => __('animal.validation.hunters_min_value'),
            'max_hunters_count.required' => __('animal.validation.max_hunters_count_required'),
            'max_hunters_count.integer' => __('animal.validation.max_hunters_count_must_be_integer'),
            'max_hunters_count.min' => __('animal.validation.max_hunters_min_value'),
            'max_hunters_count.gte' => __('animal.validation.max_hunters_gte_hunters'),
        ];
    }
}
