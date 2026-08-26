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
        ];
    }

    public function messages(): array
    {
        return [
            'hunters_count.required' => __('animal.validation.hunters_count_required'),
            'hunters_count.integer' => __('animal.validation.hunters_count_must_be_integer'),
            'hunters_count.min' => __('animal.validation.hunters_min_value'),
        ];
    }
}
