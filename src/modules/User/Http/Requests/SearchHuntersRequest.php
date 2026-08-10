<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchHuntersRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'query' => trim((string) $this->input('query')),
        ]);
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:255'],
            'booking_id' => ['required', 'integer', 'exists:bc_bookings,id'],
        ];
    }
}
