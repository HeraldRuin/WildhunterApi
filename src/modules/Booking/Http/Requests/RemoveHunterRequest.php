<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveHunterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hunter_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'hunter_id.required' => __('booking.validation.hunter_id_required'),
            'hunter_id.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_id.exists' => __('booking.validation.hunter_id_not_found'),
        ];
    }
}
