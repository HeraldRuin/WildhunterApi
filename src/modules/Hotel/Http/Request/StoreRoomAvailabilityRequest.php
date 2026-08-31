<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Hotel\Models\HotelRoom;

class StoreRoomAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'active' => ['required', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'number' => ['nullable', 'integer', 'min:0'],
            'day_of_week_select' => ['nullable', 'array'],
            'day_of_week_select.*' => ['integer', 'between:1,7'],
            'is_instant' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var HotelRoom|null $room */
            $room = $this->route('room');

            if (!$room || $validator->errors()->isNotEmpty()) {
                return;
            }

            $active = $this->boolean('active');
            $number = $this->input('number');

            if ($number === null || $number === '') {
                return;
            }

            $number = (int) $number;
            $maxNumber = (int) $room->number;

            if ($active && $number < 1) {
                $validator->errors()->add(
                    'number',
                    __('hotel.validation.room_availability_number_min_when_active'),
                );
            }

            if ($active && $maxNumber > 0 && $number > $maxNumber) {
                $validator->errors()->add(
                    'number',
                    __('hotel.validation.room_availability_number_max', ['max' => $maxNumber]),
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_date.required' => __('hotel.validation.room_availability_start_required'),
            'start_date.date' => __('hotel.validation.room_availability_start_must_be_date'),
            'start_date.date_format' => __('hotel.validation.room_availability_start_must_be_date'),
            'end_date.required' => __('hotel.validation.room_availability_end_required'),
            'end_date.date' => __('hotel.validation.room_availability_end_must_be_date'),
            'end_date.date_format' => __('hotel.validation.room_availability_end_must_be_date'),
            'end_date.after_or_equal' => __('hotel.validation.room_availability_end_after_or_equal_start'),
            'active.required' => __('hotel.validation.room_availability_active_required'),
            'active.boolean' => __('hotel.validation.room_availability_active_must_be_boolean'),
            'price.numeric' => __('hotel.validation.price_value_must_be_numeric'),
            'price.min' => __('hotel.validation.price_must_be_positive'),
            'number.integer' => __('hotel.validation.room_number_must_be_integer'),
            'number.min' => __('hotel.validation.room_availability_number_min'),
            'day_of_week_select.array' => __('hotel.validation.room_availability_days_must_be_array'),
            'day_of_week_select.*.integer' => __('hotel.validation.room_availability_day_must_be_integer'),
            'day_of_week_select.*.between' => __('hotel.validation.room_availability_day_invalid'),
            'is_instant.boolean' => __('hotel.validation.room_availability_is_instant_must_be_boolean'),
        ];
    }
}
