<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerNotesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_notes.string' => __('booking.validation.customer_notes_must_be_string'),
            'customer_notes.max' => __('booking.validation.customer_notes_max'),
        ];
    }
}
