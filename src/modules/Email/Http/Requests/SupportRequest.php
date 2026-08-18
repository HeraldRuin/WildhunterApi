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
            'name.required' => 'Укажите имя.',
            'name.string' => 'Имя должно быть строкой.',
            'name.max' => 'Имя не должно превышать 255 символов.',

            'email.required' => 'Укажите адрес электронной почты.',
            'email.email' => 'Укажите корректный адрес электронной почты.',
            'email.max' => 'Адрес электронной почты не должен превышать 255 символов.',

            'message.required' => 'Введите сообщение.',
            'message.string' => 'Сообщение должно быть строкой.',
            'message.max' => 'Сообщение не должно превышать 5000 символов.',
        ];
    }
}
