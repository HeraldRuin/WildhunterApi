<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSelectPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'place_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'place_id.required' => __('booking.validation.place_id_required'),
            'place_id.integer' => __('booking.validation.place_id_must_be_integer'),
        ];
    }
}
