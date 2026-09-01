<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCodeResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('password.validation.password_reset.email_required'),
            'email.email' => __('password.validation.password_reset.email_invalid'),
            'email.exists' => __('password.validation.password_reset.email_not_found'),
            'email.max' => __('password.validation.password_reset.email_max'),
        ];
    }
}
