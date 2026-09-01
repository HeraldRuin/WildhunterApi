<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Validation\Rule;
use Modules\Review\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class HotelSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['nullable', 'integer'],
            'animal_id' => ['nullable', 'integer'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],

            'star_rate' => ['nullable', 'array'],
            'star_rate.*' => ['string', Rule::in(array_keys(Review::RATINGS))],

            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', Rule::exists('bc_terms', 'id')],

            'price' => ['nullable', 'array'],
            'price.min' => ['nullable','numeric', 'min:0'],
            'price.max' => ['nullable', 'numeric', 'min:0', 'gte:price.min'],

            'order_by' => ['nullable', 'string'],
            'order_direction' => ['nullable', 'string', 'in:asc,desc'],
            'limit' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.integer' => __('hotel.validation.location_id_must_be_integer'),

            'animal_id.integer' => __('hotel.validation.animal_id_must_be_integer'),

            'check_in.required' => __('hotel.validation.check_in_required'),
            'check_in.date' => __('hotel.validation.check_in_must_be_date'),

            'check_out.required' => __('hotel.validation.check_out_required'),
            'check_out.date' => __('hotel.validation.check_out_must_be_date'),
            'check_out.after' => __('hotel.validation.check_out_must_be_after_check_in'),

            'adults.integer' => __('hotel.validation.adults_must_be_integer'),
            'adults.min' => __('hotel.validation.adults_min_value'),

            'children.integer' => __('hotel.validation.children_must_be_integer'),
            'children.min' => __('hotel.validation.children_min_value'),

            'order_by.string' => __('hotel.validation.order_by_must_be_string'),

            'star_rate.array' => __('hotel.validation.star_rate_must_be_array'),
            'star_rate.*.string' => __('hotel.validation.star_rate_item_must_be_string'),
            'star_rate.*.in' => __('hotel.validation.star_rate_invalid'),

            'term_ids.array' => __('hotel.validation.term_ids_must_be_array'),
            'term_ids.*.integer' => __('hotel.validation.term_id_must_be_integer'),
            'term_ids.*.exists' => __('hotel.validation.term_id_not_exists'),

            'price.array' => __('hotel.validation.price_must_be_array'),
            'price.min.numeric' => __('hotel.validation.price_min_must_be_numeric'),
            'price.max.numeric' => __('hotel.validation.price_max_must_be_numeric'),
            'price.min.min' => __('hotel.validation.price_must_be_positive'),
            'price.max.min' => __('hotel.validation.price_must_be_positive'),
            'price.max.gte' => __('hotel.validation.price_max_must_be_greater_than_min'),

            'order_direction.string' => __('hotel.validation.order_direction_must_be_string'),
            'order_direction.in' => __('hotel.validation.order_direction_invalid'),

            'limit.numeric' => __('hotel.validation.limit_must_be_numeric'),
            'limit.min' => __('hotel.validation.limit_min_value'),
        ];
    }
}
