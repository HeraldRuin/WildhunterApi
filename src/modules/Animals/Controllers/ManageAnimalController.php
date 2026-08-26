<?php

namespace Modules\Animals\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Animals\Dto\AttachAnimalData;
use Modules\Animals\Dto\UpdateHuntersCountData;
use Modules\Animals\Http\Resources\AvailableAnimalResource;
use Modules\Animals\Http\Resources\ManagedAnimalResource;
use Modules\Animals\Models\Animal;
use Modules\Animals\Requests\AttachAnimalRequest;
use Modules\Animals\Requests\UpdateHuntersCountRequest;
use Modules\Animals\Services\ManageAnimalService;

class ManageAnimalController extends Controller
{
    public function __construct(
        private readonly ManageAnimalService $manageAnimalService,
    ) {
    }

    /**
     * @throws ForbiddenException
     */
    public function index(): JsonResponse
    {
        $result = $this->manageAnimalService->getManage(Auth::user());

        return new SuccessResponse(data: [
            'animals' => ManagedAnimalResource::collection($result['animals'])->resolve(),
            'available' => AvailableAnimalResource::collection($result['available'])->resolve(),
        ]);
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function attach(AttachAnimalRequest $request): JsonResponse
    {
        $data = AttachAnimalData::fromRequest($request);
        $result = $this->manageAnimalService->attach($data->animalId, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: $result['data'],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateHuntersCount(
        UpdateHuntersCountRequest $request,
        Animal $animal,
    ): JsonResponse {
        $data = UpdateHuntersCountData::fromRequest($request);
        $result = $this->manageAnimalService->updateHuntersCount(
            $animal->id,
            $data->huntersCount,
            Auth::user(),
        );

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: $result['data'],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function detach(Animal $animal): JsonResponse
    {
        $result = $this->manageAnimalService->detach($animal->id, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: $result['data'],
        );
    }
}
