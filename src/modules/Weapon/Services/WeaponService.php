<?php

namespace Modules\Weapon\Services;

use App\Exceptions\NotFoundException;
use Modules\User\Models\UserWeapon;
use Modules\Weapon\Models\Caliber;
use Modules\Weapon\Models\WeaponType;
use Modules\Weapon\Dto\SaveUserWeaponData;
use Illuminate\Database\Eloquent\Collection;

class WeaponService
{
    public function gertWeapons(): Collection
    {
        return WeaponType::all();
    }
    public function gertCalibers(): Collection
    {
        return Caliber::all();
    }

    public function getUserWeapons(int $userId): Collection
    {
        return UserWeapon::query()
            ->with(['type', 'caliber'])
            ->where('user_id', $userId)
            ->get();
    }

    public function storeUserWeapon($userId, SaveUserWeaponData $dto): array
    {
        UserWeapon::create([
            'user_id' => $userId,
            'hunter_license_number' => $dto->hunter_license_number,
            'hunter_license_date' => $dto->hunter_license_date,
            'weapon_type_id' => $dto->weapon_type_id,
            'caliber_id' => $dto->caliber_id,
        ]);

        return [
            'code' => 'save_success',
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function updateUserWeapon(int $userId, int $weaponId, SaveUserWeaponData $dto): array
    {
        $weapon = UserWeapon::where('id', $weaponId)
            ->where('user_id', $userId)
            ->first();

        if (!$weapon) {
            throw new NotFoundException(
                errorCode: 'weapon_not_found',
                domain: 'weapon'
            );
        }

        $weapon->update([
            'hunter_license_number' => $dto->hunter_license_number,
            'hunter_license_date' => $dto->hunter_license_date,
            'weapon_type_id' => $dto->weapon_type_id,
            'caliber_id' => $dto->caliber_id,
        ]);

        return [
            'code' => 'update_success',
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function deleteUserWeapon(int $userId, int $weaponId): array
    {
        $weapon = UserWeapon::where('id', $weaponId)
            ->where('user_id', $userId)
            ->first();

        if (!$weapon) {
            throw new NotFoundException(
                errorCode: 'weapon_not_found',
                domain: 'weapon'
            );
        }

        $weapon->delete();

        return [
            'code' => 'delete_success',
        ];
    }
}
