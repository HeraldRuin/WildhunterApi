<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdditionalVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_visible' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_visible.required' => __('additional.validation.is_visible_required'),
            'is_visible.boolean' => __('additional.validation.is_visible_must_be_boolean'),
        ];
    }
}
