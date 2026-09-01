<?php

namespace Modules\User\Http\Requests\Auth;

use Modules\Role\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],

            'role' => ['required', Rule::in([Role::CUSTOMER, Role::ADMIN])],

            'phone' => ['required', 'string', 'unique:users,phone'],

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
            ],

            'term' => ['required', 'accepted'],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.required' => __('auth.validation.first_name_required'),
            'last_name.required'  => __('auth.validation.last_name_required'),

            'role.required' => __('auth.validation.role_required'),
            'role.in'       => __('auth.validation.role_invalid'),

            'phone.required' => __('auth.validation.phone_required'),
            'phone.unique'   => __('auth.validation.phone_unique'),

            'email.required' => __('auth.validation.email_required'),
            'email.email'    => __('auth.validation.email_invalid'),
            'email.unique'   => __('auth.validation.email_unique'),

            'password.required' => __('auth.validation.password_required'),
            'password.min'      => __('auth.validation.password_min'),
            'password.mixed'  => __('auth.validation.password_upper_lower'),
            'password.numbers' => __('auth.validation.password_number'),
            'password.uncompromised' => __('auth.validation.password_compromised'),

            'term.required'  => __('auth.validation.term_required'),
            'term.accepted'  => __('auth.validation.term_accepted'),
        ];
    }
}
