<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtraHuntRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'integer', 'exists:bc_animals,id'],
            'hunter_ids' => ['required', 'array', 'min:1'],
            'hunter_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_id.required' => __('booking.validation.animal_id_required'),
            'animal_id.integer' => __('booking.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('booking.validation.animal_id_not_found'),
            'hunter_ids.required' => __('booking.validation.hunter_id_required'),
            'hunter_ids.array' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_ids.min' => __('booking.validation.hunter_id_required'),
            'hunter_ids.*.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_ids.*.exists' => __('booking.validation.hunter_id_not_found'),
        ];
    }
}
