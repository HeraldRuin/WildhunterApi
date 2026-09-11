<?php

namespace Modules\Weapon\Dto;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SaveUserWeaponData
{
    public function __construct(
        public ?string $hunter_billet_number,
        public ?string $hunter_billet_issuing_authority,
        public ?string $hunter_billet_rf_subject,
        public ?Carbon $hunter_billet_issue_date,
        public ?string $identity_document,
        public ?string $hunter_license_number,
        public ?Carbon $hunter_license_date,
        public ?int $weapon_type_id,
        public ?int $caliber_id,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            hunter_billet_number: $data['hunter_billet_number'] ?? null,
            hunter_billet_issuing_authority: $data['hunter_billet_issuing_authority'] ?? null,
            hunter_billet_rf_subject: $data['hunter_billet_rf_subject'] ?? null,
            hunter_billet_issue_date: isset($data['hunter_billet_issue_date'])
                ? Carbon::parse($data['hunter_billet_issue_date'])
                : null,
            identity_document: $data['identity_document'] ?? null,
            hunter_license_number: $data['hunter_license_number'] ?? null,
            hunter_license_date: isset($data['hunter_license_date'])
                ? Carbon::parse($data['hunter_license_date'])
                : null,
            weapon_type_id: isset($data['weapon_type_id']) ? (int) $data['weapon_type_id'] : null,
            caliber_id: isset($data['caliber_id']) ? (int) $data['caliber_id'] : null,
        );
    }

    public function hasBilletData(): bool
    {
        return $this->hunter_billet_number !== null
            || $this->hunter_billet_issuing_authority !== null
            || $this->hunter_billet_rf_subject !== null
            || $this->hunter_billet_issue_date !== null
            || $this->identity_document !== null;
    }

    public function hasWeaponData(): bool
    {
        return $this->hunter_license_number !== null
            && $this->hunter_license_date !== null
            && $this->weapon_type_id !== null
            && $this->caliber_id !== null;
    }
}
