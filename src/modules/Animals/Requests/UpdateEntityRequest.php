<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Animals\Models\Animal;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|string|in:preparations,trophies,fines',
            'price' => 'nullable|numeric|min:0',
        ];

        $rules['id'] = match ($this->input('type')) {
            Animal::SERVICE_PREPARATIONS => 'required|integer|exists:bc_animal_preparations,id',
            Animal::SERVICE_TROPHIES => 'required|integer|exists:bc_animal_trophies,id',
            Animal::SERVICE_FINES => 'required|integer|exists:bc_animal_fines,id',
            default => 'required|integer',
        };

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => __('animal.validation.type_required'),
            'type.in' => __('animal.validation.type_invalid'),
            'id.required' => __('animal.validation.entity_id_required'),
            'id.integer' => __('animal.validation.entity_id_must_be_integer'),
            'id.exists' => __('animal.validation.entity_id_not_found'),
            'price.numeric' => __('animal.validation.amount_must_be_numeric'),
            'price.min' => __('animal.validation.amount_min'),
        ];
    }
}
