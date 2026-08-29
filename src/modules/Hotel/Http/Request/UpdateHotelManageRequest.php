<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Hotel\Models\Hotel;

class UpdateHotelManageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hotelId = $this->routeHotelId();

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('bc_hotels', 'slug')->ignore($hotelId),
            ],
            'content' => ['nullable', 'string'],
            'star_rate' => ['nullable', 'integer', 'min:0', 'max:5'],
            'address' => ['nullable', 'string', 'max:255'],
            'image_id' => ['nullable', 'integer', Rule::exists('media_files', 'id')],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['integer', Rule::exists('media_files', 'id')],
            'policy' => ['nullable', 'array'],
            'surrounding' => ['nullable', 'array'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'extra_price' => ['nullable', 'array'],
            'service_fee' => ['nullable', 'array'],
            'map_lat' => ['nullable', 'string', 'max:50'],
            'map_lng' => ['nullable', 'string', 'max:50'],
            'location_id' => ['nullable', 'integer', Rule::exists('bc_locations', 'id')],
            'status' => ['nullable', 'string', Rule::in(['publish', 'draft', 'pending'])],
            'has_food' => ['nullable', 'boolean'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', Rule::exists('bc_terms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('hotel.validation.title_required'),
            'title.string' => __('hotel.validation.title_must_be_string'),
            'title.max' => __('hotel.validation.title_max'),
            'slug.unique' => __('hotel.validation.slug_unique'),
            'slug.max' => __('hotel.validation.slug_max'),
            'star_rate.integer' => __('hotel.validation.star_rate_must_be_integer'),
            'star_rate.min' => __('hotel.validation.star_rate_min'),
            'star_rate.max' => __('hotel.validation.star_rate_max'),
            'image_id.exists' => __('hotel.validation.image_id_not_exists'),
            'gallery.array' => __('hotel.validation.gallery_must_be_array'),
            'gallery.*.integer' => __('hotel.validation.gallery_item_must_be_integer'),
            'gallery.*.exists' => __('hotel.validation.gallery_item_not_exists'),
            'price.numeric' => __('hotel.validation.price_value_must_be_numeric'),
            'price.min' => __('hotel.validation.price_must_be_positive'),
            'location_id.exists' => __('hotel.validation.location_id_not_exists'),
            'status.in' => __('hotel.validation.status_invalid'),
            'has_food.boolean' => __('hotel.validation.has_food_must_be_boolean'),
            'term_ids.array' => __('hotel.validation.term_ids_must_be_array'),
            'term_ids.*.integer' => __('hotel.validation.term_id_must_be_integer'),
            'term_ids.*.exists' => __('hotel.validation.term_id_not_exists'),
        ];
    }

    private function routeHotelId(): int|string|null
    {
        $hotel = $this->route('hotel');

        if ($hotel instanceof Hotel) {
            return $hotel->id;
        }

        return $hotel;
    }
}
