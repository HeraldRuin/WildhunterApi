<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Services\CollectionTimerSettingsService;

class ShowCollectionTimerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(CollectionTimerSettingsService::TYPES)],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('collection.validation.timer_type_required'),
            'type.in' => __('collection.validation.timer_type_invalid'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->route('type'),
        ]);
    }
}
