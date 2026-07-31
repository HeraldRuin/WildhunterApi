<?php

namespace Modules\Weapon\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ответ GET /user/weapons: номер билета + список лицензий.
 * Билет на уровне data, а не в каждой записи — чтобы отдавать его и при пустом списке оружия.
 */
class UserWeaponsListResource extends JsonResource
{
    public function __construct(
        private readonly ?string $hunterBilletNumber,
        private readonly Collection $weapons,
    ) {
        parent::__construct(null);
    }

    public function toArray(Request $request): array
    {
        return [
            'hunter_billet_number' => $this->hunterBilletNumber,
            'weapons' => UserWeaponResource::collection($this->weapons),
        ];
    }
}
