<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class HotelFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_by' => ['nullable', 'string'],
            'order_direction' => ['nullable', 'string', 'in:asc,desc'],
            'limit' => ['nullable', 'numeric', 'min:1'],
            'is_featured' => ['nullable', 'boolean'],
            'custom_ids' => ['nullable', 'array'],
            'custom_ids.*' => ['integer', 'min:1'],
            'location_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_by.string' => __('hotel.validation.order_by_must_be_string'),

            'order_direction.string' => __('hotel.validation.order_direction_must_be_string'),
            'order_direction.in' => __('hotel.validation.order_direction_invalid'),

            'limit.numeric' => __('hotel.validation.limit_must_be_numeric'),
            'limit.min' => __('hotel.validation.limit_min_value'),

            'is_featured.boolean' => __('hotel.validation.is_featured_must_be_boolean'),

            'custom_ids.array' => __('hotel.validation.custom_ids_must_be_array'),
            'custom_ids.*.integer' => __('hotel.validation.custom_id_must_be_integer'),
            'custom_ids.*.min' => __('hotel.validation.custom_id_must_be_integer'),

            'location_id.integer' => __('hotel.validation.location_id_must_be_integer'),
            'location_id.min' => __('hotel.validation.location_id_must_be_integer'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('custom_ids') && is_string($this->input('custom_ids'))) {
            $ids = array_filter(array_map('trim', explode(',', $this->input('custom_ids'))));
            $this->merge(['custom_ids' => $ids]);
        }

        if ($this->has('is_featured') && is_string($this->input('is_featured'))) {
            $value = strtolower($this->input('is_featured'));
            if (in_array($value, ['1', 'true', 'on', 'yes'], true)) {
                $this->merge(['is_featured' => true]);
            } elseif (in_array($value, ['0', 'false', 'off', 'no'], true)) {
                $this->merge(['is_featured' => false]);
            }
        }
    }
}
