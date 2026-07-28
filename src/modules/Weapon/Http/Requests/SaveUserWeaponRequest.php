<?php

namespace Modules\Weapon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveUserWeaponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'hunter_billet_number' => [
                'nullable',
                'string',
                'max:255',
                'required_without_all:hunter_license_number,hunter_license_date,weapon_type_id,caliber_id',
            ],
            'hunter_license_number' => [
                'nullable',
                'string',
                'max:255',
                'required_without:hunter_billet_number',
                'required_with:hunter_license_date,weapon_type_id,caliber_id',
            ],
            'hunter_license_date' => [
                'nullable',
                'date',
                'required_without:hunter_billet_number',
                'required_with:hunter_license_number,weapon_type_id,caliber_id',
            ],
            'weapon_type_id' => [
                'nullable',
                'integer',
                'exists:bc_weapons,id',
                'required_without:hunter_billet_number',
                'required_with:hunter_license_number,hunter_license_date,caliber_id',
            ],
            'caliber_id' => [
                'nullable',
                'integer',
                'exists:bc_calibers,id',
                'required_without:hunter_billet_number',
                'required_with:hunter_license_number,hunter_license_date,weapon_type_id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'hunter_billet_number.required_without_all' => __('weapon.validation.hunter_billet_number_required'),
            'hunter_billet_number.string' => __('weapon.validation.hunter_billet_number_string'),
            'hunter_billet_number.max' => __('weapon.validation.hunter_billet_number_max'),

            'hunter_license_number.required_without' => __('weapon.validation.hunter_license_number_required'),
            'hunter_license_number.required_with' => __('weapon.validation.hunter_license_number_required'),
            'hunter_license_number.string' => __('weapon.validation.hunter_license_number_string'),

            'hunter_license_date.required_without' => __('weapon.validation.hunter_license_date_required'),
            'hunter_license_date.required_with' => __('weapon.validation.hunter_license_date_required'),
            'hunter_license_date.date' => __('weapon.validation.hunter_license_date_invalid'),

            'weapon_type_id.required_without' => __('weapon.validation.weapon_type_required'),
            'weapon_type_id.required_with' => __('weapon.validation.weapon_type_required'),
            'weapon_type_id.exists' => __('weapon.validation.weapon_type_not_found'),

            'caliber_id.required_without' => __('weapon.validation.caliber_required'),
            'caliber_id.required_with' => __('weapon.validation.caliber_required'),
            'caliber_id.integer' => __('weapon.validation.caliber_integer'),
            'caliber_id.exists' => __('weapon.validation.caliber_not_found'),
        ];
    }
}
