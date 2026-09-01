<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Services\CollectionTimerSettingsService;

class StoreCollectionTimerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(CollectionTimerSettingsService::TYPES)],
            'timer_hours' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('collection.validation.timer_type_required'),
            'type.in' => __('collection.validation.timer_type_invalid'),
            'timer_hours.required' => __('collection.validation.timer_hours_required'),
            'timer_hours.integer' => __('collection.validation.timer_hours_must_be_integer'),
            'timer_hours.min' => __('collection.validation.timer_hours_min_value'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->route('type'),
        ]);
    }
}
