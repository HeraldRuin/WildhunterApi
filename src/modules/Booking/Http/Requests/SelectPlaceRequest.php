<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectPlaceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'min:1'],
            'place_number' => ['required', 'integer', 'min:1'],
            'room_index' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => __('booking.validation.room_id_required'),
            'room_id.integer' => __('booking.validation.room_id_must_be_integer'),
            'place_number.required' => __('booking.validation.place_number_required'),
            'place_number.integer' => __('booking.validation.place_number_must_be_integer'),
            'room_index.required' => __('booking.validation.room_index_required'),
            'room_index.integer' => __('booking.validation.room_index_must_be_integer'),
        ];
    }
}
