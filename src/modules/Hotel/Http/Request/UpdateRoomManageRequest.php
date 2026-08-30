<?php

namespace Modules\Hotel\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomManageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image_id' => ['nullable', 'integer', Rule::exists('media_files', 'id')],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['integer', Rule::exists('media_files', 'id')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'number' => ['nullable', 'integer', 'min:1'],
            'beds' => ['nullable', 'integer', 'min:0'],
            'size' => ['nullable', 'integer', 'min:0'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['publish', 'draft', 'pending'])],
            'min_day_stays' => ['nullable', 'integer', 'min:1'],
            'ical_import_url' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', Rule::exists('bc_terms', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('hotel.validation.room_title_required'),
            'title.string' => __('hotel.validation.room_title_must_be_string'),
            'title.max' => __('hotel.validation.room_title_max'),
            'image_id.exists' => __('hotel.validation.image_id_not_exists'),
            'gallery.array' => __('hotel.validation.gallery_must_be_array'),
            'gallery.*.integer' => __('hotel.validation.gallery_item_must_be_integer'),
            'gallery.*.exists' => __('hotel.validation.gallery_item_not_exists'),
            'price.numeric' => __('hotel.validation.price_value_must_be_numeric'),
            'price.min' => __('hotel.validation.price_must_be_positive'),
            'number.integer' => __('hotel.validation.room_number_must_be_integer'),
            'number.min' => __('hotel.validation.room_number_min'),
            'beds.integer' => __('hotel.validation.room_beds_must_be_integer'),
            'beds.min' => __('hotel.validation.room_beds_min'),
            'size.integer' => __('hotel.validation.room_size_must_be_integer'),
            'size.min' => __('hotel.validation.room_size_min'),
            'adults.integer' => __('hotel.validation.adults_must_be_integer'),
            'adults.min' => __('hotel.validation.adults_min_value'),
            'children.integer' => __('hotel.validation.children_must_be_integer'),
            'children.min' => __('hotel.validation.children_min_value'),
            'status.in' => __('hotel.validation.status_invalid'),
            'min_day_stays.integer' => __('hotel.validation.min_day_stays_must_be_integer'),
            'min_day_stays.min' => __('hotel.validation.min_day_stays_min'),
            'term_ids.array' => __('hotel.validation.term_ids_must_be_array'),
            'term_ids.*.integer' => __('hotel.validation.term_id_must_be_integer'),
            'term_ids.*.exists' => __('hotel.validation.term_id_not_exists'),
        ];
    }
}
