<?php

namespace Modules\Weapon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveUserWeaponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hunter_license_number' => ['required', 'string', 'max:255'],
            'hunter_license_date' => ['required', 'date'],
            'weapon_type_id' => ['required', 'integer', 'exists:bc_weapons,id'],
            'caliber_id' => ['required', 'integer', 'exists:bc_calibers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'hunter_license_number.required' => __('weapon.validation.hunter_license_number_required'),
            'hunter_license_number.string' => __('weapon.validation.hunter_license_number_string'),

            'hunter_license_date.required' => __('weapon.validation.hunter_license_date_required'),
            'hunter_license_date.date' => __('weapon.validation.hunter_license_date_invalid'),

            'weapon_type_id.required' => __('weapon.validation.weapon_type_required'),
            'weapon_type_id.exists' => __('weapon.validation.weapon_type_not_found'),

            'caliber_id.required' => __('weapon.validation.caliber_required'),
            'caliber_id.integer' => __('weapon.validation.caliber_integer'),
            'caliber_id.exists' => __('weapon.validation.caliber_not_found'),
        ];
    }
}
