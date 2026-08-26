<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'integer', 'min:1', 'exists:bc_animals,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_id.required' => __('animal.validation.animal_id_required'),
            'animal_id.integer' => __('animal.validation.animal_id_must_be_integer'),
            'animal_id.min' => __('animal.validation.animal_id_must_be_integer'),
            'animal_id.exists' => __('animal.errors.animal_not_found'),
        ];
    }
}
