<?php

namespace Modules\Animals\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Animals\Dto\UpdateEntityData;
use Modules\Animals\Http\Resources\TrophyCostAnimalResource;
use Modules\Animals\Requests\UpdateEntityRequest;
use Modules\Animals\Services\TrophyCostService;

class TrophyCostController extends Controller
{
    public function __construct(
        private readonly TrophyCostService $trophyCostService,
    ) {
    }

    /**
     * @throws ForbiddenException
     */
    public function index(): JsonResponse
    {
        $animals = $this->trophyCostService->getTrophyCost(Auth::user());

        return new SuccessResponse(data: TrophyCostAnimalResource::collection($animals));
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateTrophy(UpdateEntityRequest $request): JsonResponse
    {
        $data = UpdateEntityData::fromRequest($request);
        $result = $this->trophyCostService->update($data, $data->getEntity(), 'trophy', Auth::user());

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateFine(UpdateEntityRequest $request): JsonResponse
    {
        $data = UpdateEntityData::fromRequest($request);
        $result = $this->trophyCostService->update($data, $data->getEntity(), 'fine', Auth::user());

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updatePreparation(UpdateEntityRequest $request): JsonResponse
    {
        $data = UpdateEntityData::fromRequest($request);
        $result = $this->trophyCostService->update($data, $data->getEntity(), 'preparation', Auth::user());

        return new SuccessResponse(code: $result['code'], domain: 'animal');
    }
}
