<?php

namespace Modules\Weapon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveUserWeaponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hunter_billet_number' => ['nullable', 'string', 'max:255'],
            'hunter_license_number' => ['nullable', 'string', 'max:255'],
            'hunter_license_date' => ['nullable', 'date'],
            'weapon_type_id' => ['nullable', 'integer', 'exists:bc_weapons,id'],
            'caliber_id' => ['nullable', 'integer', 'exists:bc_calibers,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $isBilletSave = $this->exists('hunter_billet_number');
            $isWeaponSave = $this->hasWeaponSaveIntent();

            if (!$isBilletSave && !$isWeaponSave) {
                $validator->errors()->add(
                    'hunter_billet_number',
                    __('weapon.validation.hunter_billet_number_required')
                );

                return;
            }

            if ($isBilletSave && !filled($this->input('hunter_billet_number'))) {
                $validator->errors()->add(
                    'hunter_billet_number',
                    __('weapon.validation.hunter_billet_number_required')
                );
            }

            if (!$isWeaponSave) {
                return;
            }

            foreach ($this->weaponFields() as $field => $message) {
                if (!filled($this->input($field))) {
                    $validator->errors()->add($field, __($message));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'hunter_billet_number.string' => __('weapon.validation.hunter_billet_number_string'),
            'hunter_billet_number.max' => __('weapon.validation.hunter_billet_number_max'),
            'hunter_license_number.string' => __('weapon.validation.hunter_license_number_string'),
            'hunter_license_date.date' => __('weapon.validation.hunter_license_date_invalid'),
            'weapon_type_id.exists' => __('weapon.validation.weapon_type_not_found'),
            'caliber_id.integer' => __('weapon.validation.caliber_integer'),
            'caliber_id.exists' => __('weapon.validation.caliber_not_found'),
        ];
    }

    private function hasWeaponSaveIntent(): bool
    {
        foreach (array_keys($this->weaponFields()) as $field) {
            if ($this->exists($field)) {
                return true;
            }
        }

        return false;
    }

    private function weaponFields(): array
    {
        return [
            'hunter_license_number' => 'weapon.validation.hunter_license_number_required',
            'hunter_license_date' => 'weapon.validation.hunter_license_date_required',
            'weapon_type_id' => 'weapon.validation.weapon_type_required',
            'caliber_id' => 'weapon.validation.caliber_required',
        ];
    }
}
