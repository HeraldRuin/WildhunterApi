<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Models\AddetionalPrice;

class UpdateAdditionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('calculation_type') && $this->input('calculation_type') === '') {
            $this->merge(['calculation_type' => null]);
        }

        if ($this->has('count') && $this->input('count') === '') {
            $this->merge(['count' => null]);
        }

        if (!$this->filled('place')) {
            $this->merge(['place' => AddetionalPrice::PLACE_BASE]);
        }
    }

    public function rules(): array
    {
        /** @var AddetionalPrice|null $additional */
        $additional = $this->route('additional');
        $isFood = (bool) $additional?->isFood();
        $calculationTypeRules = $isFood
            ? ['nullable', 'string', Rule::in([AddetionalPrice::INDIVIDUAL, AddetionalPrice::PERSON])]
            : ['required', 'string', Rule::in([AddetionalPrice::INDIVIDUAL, AddetionalPrice::PERSON])];

        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'count' => ['nullable', 'integer', 'min:0'],
            'calculation_type' => $calculationTypeRules,
            'is_system' => ['required', 'boolean'],
            'place' => ['required', 'string', Rule::in(AddetionalPrice::PLACES)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('additional.validation.name_required'),
            'name.string' => __('additional.validation.name_must_be_string'),
            'name.max' => __('additional.validation.name_max'),
            'price.required' => __('additional.validation.price_required'),
            'price.numeric' => __('additional.validation.price_must_be_numeric'),
            'price.min' => __('additional.validation.price_min'),
            'count.integer' => __('additional.validation.count_must_be_integer'),
            'count.min' => __('additional.validation.count_min'),
            'calculation_type.required' => __('additional.validation.calculation_type_required'),
            'calculation_type.in' => __('additional.validation.calculation_type_invalid'),
            'is_system.required' => __('additional.validation.is_system_required'),
            'is_system.boolean' => __('additional.validation.is_system_must_be_boolean'),
            'place.in' => __('additional.validation.place_invalid'),
        ];
    }
}
