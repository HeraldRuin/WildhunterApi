<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'exists:bc_hotels,id'],
            'animal_id' => ['nullable', 'integer', 'exists:bc_animals,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'hunters' => ['nullable', 'integer', 'min:1'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.room_id' => [
                'required',
                'integer',
                Rule::exists('bc_hotel_rooms', 'id')->where(function ($query) {
                    $query->where('parent_id', $this->input('hotel_id'))
                        ->where('status', 'publish');
                }),
            ],
            'rooms.*.number' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'hotel_id.required' => __('booking.validation.hotel_id_required'),
            'hotel_id.integer' => __('booking.validation.hotel_id_must_be_integer'),
            'hotel_id.exists' => __('booking.validation.hotel_id_not_found'),

            'animal_id.integer' => __('booking.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('booking.validation.animal_id_not_found'),

            'check_in.required' => __('booking.validation.check_in_required'),
            'check_in.date' => __('booking.validation.check_in_must_be_date'),
            'check_in.after_or_equal' => __('booking.validation.check_in_must_be_today_or_later'),

            'check_out.required' => __('booking.validation.check_out_required'),
            'check_out.date' => __('booking.validation.check_out_must_be_date'),
            'check_out.after' => __('booking.validation.check_out_must_be_after_check_in'),

            'adults.integer' => __('booking.validation.adults_must_be_integer'),
            'adults.min' => __('booking.validation.adults_min_value'),

            'hunters.integer' => __('booking.validation.hunters_must_be_integer'),
            'hunters.min' => __('booking.validation.hunters_min_value'),

            'rooms.required' => __('booking.validation.rooms_required'),
            'rooms.array' => __('booking.validation.rooms_must_be_array'),
            'rooms.min' => __('booking.validation.rooms_min_value'),

            'rooms.*.room_id.required' => __('booking.validation.room_id_required'),
            'rooms.*.room_id.integer' => __('booking.validation.room_id_must_be_integer'),
            'rooms.*.room_id.exists' => __('booking.validation.room_id_not_found'),

            'rooms.*.number.required' => __('booking.validation.room_number_required'),
            'rooms.*.number.integer' => __('booking.validation.room_number_must_be_integer'),
            'rooms.*.number.min' => __('booking.validation.room_number_min_value'),
        ];
    }
}
