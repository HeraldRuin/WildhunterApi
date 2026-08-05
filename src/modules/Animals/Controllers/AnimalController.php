<?php

namespace Modules\Animals\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Modules\Animals\Dto\CheckAnimalAvailabilityData;
use Modules\Animals\Http\Request\CheckAnimalAvailabilityRequest;
use Modules\Animals\Http\Resources\AnimalResource;
use Modules\Animals\Services\AnimalService;

class AnimalController extends Controller
{
    public function __construct(private AnimalService $animalService)
    {
    }

    public function getAnimals(): JsonResponse
    {
        $result = $this->animalService->getAnimals();

        return new SuccessResponse(data: AnimalResource::collection($result['animals']));
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function checkAvailability(CheckAnimalAvailabilityRequest $request): JsonResponse
    {
        $dto = CheckAnimalAvailabilityData::fromRequest($request);
        $result = $this->animalService->checkAvailability($dto);

        return new SuccessResponse(data: $result);
    }
}
