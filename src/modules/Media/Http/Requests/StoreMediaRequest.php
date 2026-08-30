<?php

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('media.validation.file_required'),
            'file.file' => __('media.validation.file_must_be_file'),
            'file.image' => __('media.validation.file_must_be_image'),
            'file.mimes' => __('media.validation.file_mimes'),
            'file.max' => __('media.validation.file_max'),
        ];
    }
}
