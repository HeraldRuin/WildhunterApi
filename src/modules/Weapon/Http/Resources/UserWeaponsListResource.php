<?php

namespace Modules\Weapon\Http\Resources;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ответ GET /user/weapons: данные охотничьего билета + список лицензий.
 * Билет на уровне data, а не в каждой записи — чтобы отдавать его и при пустом списке оружия.
 */
class UserWeaponsListResource extends JsonResource
{
    public function __construct(
        private readonly User $user,
        private readonly Collection $weapons,
    ) {
        parent::__construct(null);
    }

    public function toArray(Request $request): array
    {
        return [
            'hunter_billet_number' => $this->user->hunter_billet_number,
            'hunter_billet_issuing_authority' => $this->user->hunter_billet_issuing_authority,
            'hunter_billet_rf_subject' => $this->user->hunter_billet_rf_subject,
            'hunter_billet_issue_date' => $this->user->hunter_billet_issue_date?->translatedFormat('d F Y г.'),
            'identity_document' => $this->user->identity_document,
            'weapons' => UserWeaponResource::collection($this->weapons),
        ];
    }
}
