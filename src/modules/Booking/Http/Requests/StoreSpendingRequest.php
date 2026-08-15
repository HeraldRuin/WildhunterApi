<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpendingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'hunter_id' => ['required', 'integer', 'exists:users,id'],
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => __('booking.validation.spending_price_required'),
            'price.numeric' => __('booking.validation.spending_price_must_be_number'),
            'price.min' => __('booking.validation.spending_price_min_value'),
            'hunter_id.required' => __('booking.validation.hunter_id_required'),
            'hunter_id.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_id.exists' => __('booking.validation.hunter_id_not_found'),
            'comment.required' => __('booking.validation.spending_comment_required'),
            'comment.string' => __('booking.validation.spending_comment_must_be_string'),
            'comment.max' => __('booking.validation.spending_comment_max'),
        ];
    }
}
