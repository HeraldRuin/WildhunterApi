<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeBookingCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('booking.validation.user_id_required'),
            'user_id.integer' => __('booking.validation.user_id_must_be_integer'),
        ];
    }
}
