<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers(),
                'confirmed',
            ],
            'password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('password.validation.password_reset.email_required'),
            'email.email' => __('password.validation.password_reset.email_invalid'),
            'email.exists' => __('password.validation.password_reset.email_not_found'),
            'email.max' => __('password.validation.password_reset.email_max'),

            'code.required' => __('password.validation.password_reset.code_required'),
            'code.digits' => __('password.validation.password_reset.code_digits'),

            'password.required' => __('auth.validation.password_required'),
            'password.min'      => __('auth.validation.password_min'),
            'password.mixed'  => __('auth.validation.password_upper_lower'),
            'password.numbers' => __('auth.validation.password_number'),

            'password.confirmed' => __('password.validation.new_password_confirmed'),
            'password_confirmation.required' => __('password.validation.confirmation_password_required'),
        ];
    }
}
