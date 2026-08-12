<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceHunterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'old_hunter_id' => ['required', 'integer', 'different:hunter_id'],
            'hunter_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'old_hunter_id.required' => __('booking.validation.old_hunter_id_required'),
            'old_hunter_id.integer' => __('booking.validation.old_hunter_id_must_be_integer'),
            'old_hunter_id.different' => __('booking.validation.hunter_ids_must_be_different'),
            'hunter_id.required' => __('booking.validation.hunter_id_required'),
            'hunter_id.integer' => __('booking.validation.hunter_id_must_be_integer'),
            'hunter_id.exists' => __('booking.validation.hunter_id_not_found'),
        ];
    }
}
