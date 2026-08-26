<?php

namespace Modules\Animals\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Animals\Dto\AnimalPricePeriodUpdateData;
use Modules\Animals\Http\Resources\AnimalPricePeriodResource;
use Modules\Animals\Http\Resources\OrganisationAnimalResource;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalPricePeriod;
use Modules\Animals\Requests\UpdateAnimalPricePeriodRequest;
use Modules\Animals\Services\OrganisationService;

class OrganisationController extends Controller
{
    public function __construct(
        private readonly OrganisationService $organisationService,
    ) {
    }

    /**
     * @throws ForbiddenException
     */
    public function index(): JsonResponse
    {
        $animals = $this->organisationService->getOrganisation(Auth::user());

        return new SuccessResponse(data: OrganisationAnimalResource::collection($animals));
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function createPeriod(Animal $animal): JsonResponse
    {
        $result = $this->organisationService->createPeriod($animal->id, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: [
                'animal_id' => $result['data']['animal_id'],
                'period' => (new AnimalPricePeriodResource($result['data']['period']))->resolve(),
            ],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updatePeriod(
        UpdateAnimalPricePeriodRequest $request,
        AnimalPricePeriod $period,
    ): JsonResponse {
        $data = AnimalPricePeriodUpdateData::fromRequest($request);
        $result = $this->organisationService->updatePeriod($period, $data, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: [
                'period' => (new AnimalPricePeriodResource($result['data']['period']))->resolve(),
            ],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function deletePeriod(AnimalPricePeriod $period): JsonResponse
    {
        $result = $this->organisationService->deletePeriod($period, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'animal',
            data: $result['data'],
        );
    }
}
