<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreparationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'integer', 'exists:bc_animals,id'],
            'count' => ['required', 'integer', 'min:1'],
            'preparation_id' => ['required', 'integer', 'exists:bc_animal_preparations,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_id.required' => __('booking.validation.animal_id_required'),
            'animal_id.integer' => __('booking.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('booking.validation.animal_id_not_found'),
            'count.required' => __('booking.validation.service_count_required'),
            'count.integer' => __('booking.validation.service_count_must_be_integer'),
            'count.min' => __('booking.validation.service_count_min_value'),
            'preparation_id.required' => __('booking.validation.preparation_id_required'),
            'preparation_id.integer' => __('booking.validation.preparation_id_must_be_integer'),
            'preparation_id.exists' => __('booking.validation.preparation_id_not_found'),
        ];
    }
}
