<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,strict',
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                'max:255',
            ],
            'privacy_policy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('user.validation.subscription.email_required'),
            'email.email' => __('user.validation.subscription.email_invalid'),
            'email.regex' => __('user.validation.subscription.email_invalid'),
            'email.max' => __('user.validation.subscription.email_max'),

            'privacy_policy.accepted' => __('user.validation.subscription.privacy_policy_accepted'),
        ];
    }
}
