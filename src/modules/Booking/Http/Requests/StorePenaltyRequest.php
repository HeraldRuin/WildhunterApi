<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenaltyRequest extends FormRequest
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
            'hunter_id' => ['required', 'integer', 'exists:users,id'],
            'penalty_id' => ['required', 'integer', 'exists:bc_animal_fines,id'],
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
            'hunter_id.required' => __('booking.validation.hunter_id_required'),
            'hunter_id.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_id.exists' => __('booking.validation.hunter_id_not_found'),
            'penalty_id.required' => __('booking.validation.penalty_id_required'),
            'penalty_id.integer' => __('booking.validation.penalty_id_must_be_integer'),
            'penalty_id.exists' => __('booking.validation.penalty_id_not_found'),
        ];
    }
}
