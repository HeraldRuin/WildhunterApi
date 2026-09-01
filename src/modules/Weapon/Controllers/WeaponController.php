<?php

namespace Modules\Weapon\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Weapon\Dto\SaveUserWeaponData;
use Modules\Weapon\Http\Requests\SaveUserWeaponRequest;
use Modules\Weapon\Http\Resources\CaliberResource;
use Modules\Weapon\Http\Resources\UserWeaponsListResource;
use Modules\Weapon\Services\WeaponService;
use Modules\Weapon\Http\Resources\WeaponResource;

class WeaponController extends Controller
{
    public function __construct(private WeaponService $weaponService)
    {
    }

    public function weapons(): JsonResponse
    {
        $result = $this->weaponService->gertWeapons();

        return new SuccessResponse(data: WeaponResource::collection($result));
    }
    public function calibers(): JsonResponse
    {
        $result = $this->weaponService->gertCalibers();

        return new SuccessResponse(data: CaliberResource::collection($result));
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $weapons = $this->weaponService->getUserWeapons((int) $user->id);

        $hunterBilletNumber = User::query()
            ->whereKey($user->id)
            ->value('hunter_billet_number');

        return new SuccessResponse(data: new UserWeaponsListResource(
            $hunterBilletNumber,
            $weapons,
        ));
    }

    public function store(SaveUserWeaponRequest $request): JsonResponse
    {
        $dto = SaveUserWeaponData::fromRequest($request);
        $result = $this->weaponService->storeUserWeapon(Auth::id(), $dto);

        return new SuccessResponse(code: $result['code'], domain: 'weapon');
    }

    /**
     * @throws NotFoundException
     */
    public function update(SaveUserWeaponRequest $request, int $id): JsonResponse
    {
        $dto = SaveUserWeaponData::fromRequest($request);
        $result = $this->weaponService->updateUserWeapon(Auth::id(), $id, $dto);

        return new SuccessResponse(code: $result['code'], domain: 'weapon');
    }

    /**
     * @throws NotFoundException
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->weaponService->deleteUserWeapon(Auth::id(), $id);

        return new SuccessResponse(code: $result['code'], domain: 'weapon');
    }
}
