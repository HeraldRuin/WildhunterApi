<?php

namespace Modules\User\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => __('auth.validation.email_required'),
            'email.email'    => __('auth.validation.email_invalid'),
            'email.max'      => __('auth.validation.email_max'),

            'password.required' => __('auth.validation.password_required'),
            'password.string'   => __('auth.validation.password_string'),
            'password.min'      => __('auth.validation.password_min_login'),
        ];
    }
}
