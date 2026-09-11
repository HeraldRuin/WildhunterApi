<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateHuntersCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animals' => ['required', 'array', 'min:1'],
            'animals.*.id' => ['required', 'integer', 'min:1'],
            'animals.*.hunters_count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'animals.required' => __('animal.validation.animals_required'),
            'animals.array' => __('animal.validation.animals_must_be_array'),
            'animals.min' => __('animal.validation.animals_min'),
            'animals.*.id.required' => __('animal.validation.animal_id_required'),
            'animals.*.id.integer' => __('animal.validation.animal_id_must_be_integer'),
            'animals.*.id.min' => __('animal.validation.animal_id_must_be_integer'),
            'animals.*.hunters_count.required' => __('animal.validation.hunters_count_required'),
            'animals.*.hunters_count.integer' => __('animal.validation.hunters_count_must_be_integer'),
            'animals.*.hunters_count.min' => __('animal.validation.hunters_min_value'),
        ];
    }
}
