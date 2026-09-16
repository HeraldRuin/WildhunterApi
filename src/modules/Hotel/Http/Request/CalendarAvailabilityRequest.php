<?php

namespace Modules\Hotel\Http\Request;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CalendarAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('adults') === '') {
            $this->merge(['adults' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer'],
            'start' => ['required', 'date', 'date_format:Y-m-d'],
            'end' => ['required', 'date', 'date_format:Y-m-d', 'after:start'],
            'adults' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $start = Carbon::createFromFormat('Y-m-d', (string) $this->input('start'))->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', (string) $this->input('end'))->startOfDay();

            if ($start->copy()->addMonths(3)->lt($end)) {
                $validator->errors()->add(
                    'end',
                    __('hotel.validation.calendar_period_max_months'),
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'hotel_id.required' => __('hotel.validation.hotel_id_required'),
            'hotel_id.integer' => __('hotel.validation.hotel_id_must_be_integer'),

            'start.required' => __('hotel.validation.start_required'),
            'start.date' => __('hotel.validation.start_must_be_date'),
            'start.date_format' => __('hotel.validation.start_must_be_date'),

            'end.required' => __('hotel.validation.end_required'),
            'end.date' => __('hotel.validation.end_must_be_date'),
            'end.date_format' => __('hotel.validation.end_must_be_date'),
            'end.after' => __('hotel.validation.end_must_be_after_start'),

            'adults.integer' => __('hotel.validation.adults_must_be_integer'),
            'adults.min' => __('hotel.validation.adults_min_value'),
        ];
    }
}
