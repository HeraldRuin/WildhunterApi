<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:255'],
        ];
    }
}
