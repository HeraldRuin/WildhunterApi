<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerNotesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'customer_notes' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => __('booking.validation.code_required'),
            'code.string' => __('booking.validation.code_must_be_string'),

            'customer_notes.required' => __('booking.validation.customer_notes_required'),
            'customer_notes.string' => __('booking.validation.customer_notes_must_be_string'),
            'customer_notes.max' => __('booking.validation.customer_notes_max'),
        ];
    }
}
