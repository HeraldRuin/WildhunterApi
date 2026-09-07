<?php

namespace Modules\Animals\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class CheckAnimalAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'exists:bc_hotels,id'],
            'animal_id' => ['required', 'integer', 'exists:bc_animals,id'],
            'hunter_data' => ['required', 'date', 'date_format:Y-m-d'],
            'hunters' => ['required', 'integer', 'min:1'],
            'check_in' => ['nullable', 'date', 'date_format:Y-m-d'],
            'check_out' => ['nullable', 'date', 'date_format:Y-m-d', 'after:check_in'],
        ];
    }

    public function messages(): array
    {
        return [
            'hotel_id.required' => __('animal.validation.hotel_id_required'),
            'hotel_id.integer' => __('animal.validation.hotel_id_must_be_integer'),
            'hotel_id.exists' => __('animal.errors.hotel_not_found'),

            'animal_id.required' => __('animal.validation.animal_id_required'),
            'animal_id.integer' => __('animal.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('animal.errors.animal_not_found'),

            'hunter_data.required' => __('animal.validation.hunter_data_required'),
            'hunter_data.date' => __('animal.validation.hunter_data_must_be_date'),
            'hunter_data.date_format' => __('animal.validation.hunter_data_must_be_date'),

            'hunters.required' => __('animal.validation.hunters_required'),
            'hunters.integer' => __('animal.validation.hunters_must_be_integer'),
            'hunters.min' => __('animal.validation.hunters_min_value'),

            'check_in.date' => __('animal.validation.check_in_must_be_date'),
            'check_in.date_format' => __('animal.validation.check_in_must_be_date'),

            'check_out.date' => __('animal.validation.check_out_must_be_date'),
            'check_out.date_format' => __('animal.validation.check_out_must_be_date'),
            'check_out.after' => __('animal.validation.check_out_must_be_after_check_in'),
        ];
    }
}
