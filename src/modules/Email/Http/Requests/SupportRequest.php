<?php

namespace Modules\Email\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('support.validation.name_required'),
            'name.string' => __('support.validation.name_string'),
            'name.max' => __('support.validation.name_max'),

            'email.required' => __('support.validation.email_required'),
            'email.email' => __('support.validation.email_invalid'),
            'email.max' => __('support.validation.email_max'),

            'message.required' => __('support.validation.message_required'),
            'message.string' => __('support.validation.message_string'),
            'message.max' => __('support.validation.message_max'),
        ];
    }
}
