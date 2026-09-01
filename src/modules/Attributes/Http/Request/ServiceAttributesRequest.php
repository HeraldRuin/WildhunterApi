<?php

namespace Modules\Attributes\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class ServiceAttributesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('review.validation.type_required'),
            'type.string' => __('review.validation.type_string'),
        ];
    }
}
