<?php

namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'new_password' => [
                'required',
                'string',

                Password::min(8)
                    ->mixedCase()
                    ->numbers(),

                'confirmed',
                'different:current_password',
            ],

            'new_password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('password.validation.current_password_required'),

            'new_password.required' => __('password.validation.new_password_required'),
            'new_password.min' => __('auth.validation.password_min'),
            'new_password.password.mixed' => __('auth.validation.password_upper_lower'),
            'new_password.password.numbers' => __('auth.validation.password_number'),
            'new_password.confirmed' => __('password.validation.new_password_confirmed'),
            'new_password.different' => __('password.validation.new_password_different'),

            'new_password_confirmation.required' => __('password.validation.confirmation_password_required'),
        ];
    }
}
