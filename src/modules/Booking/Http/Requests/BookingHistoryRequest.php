<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuses = array_merge(config('booking.statuses', []), [
            'finish_prepayment',
            'finished_collection',
        ]);

        return [
            'status' => ['nullable', 'string', Rule::in($statuses)],
            'code' => ['nullable', 'string', 'max:255'],
            'booking_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.string' => __('booking.validation.status_must_be_string'),
            'status.in' => __('booking.validation.status_invalid'),
            'code.string' => __('booking.validation.code_must_be_string'),
            'code.max' => __('booking.validation.code_max_length'),
            'booking_id.integer' => __('booking.validation.booking_id_must_be_integer'),
            'booking_id.min' => __('booking.validation.booking_id_min_value'),
            'page.integer' => __('booking.validation.page_must_be_integer'),
            'page.min' => __('booking.validation.page_min_value'),
        ];
    }
}
