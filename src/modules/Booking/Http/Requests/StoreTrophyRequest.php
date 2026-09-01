<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrophyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'integer', 'exists:bc_animals,id'],
            'type' => ['required', 'string'],
            'count' => ['required', 'integer', 'min:1'],
            'trophy_id' => ['required', 'integer', 'exists:bc_animal_trophies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_id.required' => __('booking.validation.animal_id_required'),
            'animal_id.integer' => __('booking.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('booking.validation.animal_id_not_found'),
            'type.required' => __('booking.validation.service_type_required'),
            'type.string' => __('booking.validation.service_type_must_be_string'),
            'count.required' => __('booking.validation.service_count_required'),
            'count.integer' => __('booking.validation.service_count_must_be_integer'),
            'count.min' => __('booking.validation.service_count_min_value'),
            'trophy_id.required' => __('booking.validation.trophy_id_required'),
            'trophy_id.integer' => __('booking.validation.trophy_id_must_be_integer'),
            'trophy_id.exists' => __('booking.validation.trophy_id_not_found'),
        ];
    }
}
