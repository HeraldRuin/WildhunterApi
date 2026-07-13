<?php

namespace Modules\Location\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Modules\Location\Dto\LocationFilterData;
use Modules\Location\Services\LocationService;
use Modules\Location\Http\Request\LocationFilterRequest;
use Modules\Location\Http\Resources\BestLocationResource;
use Modules\Location\Http\Resources\LocationResource;


class LocationController extends Controller
{
    public function __construct(protected LocationService $locationService)
    {
    }
    public function getBestLocations(LocationFilterRequest $request): JsonResponse
    {
        $dto = LocationFilterData::fromRequest($request);
        $result = $this->locationService->getBestLocations($dto);

        return new SuccessResponse(data: BestLocationResource::collection($result['locations'])
        );
    }

    public function getLocations(LocationFilterRequest $request): JsonResponse
    {
        $dto = LocationFilterData::fromRequest($request);
        $result = $this->locationService->getLocations($dto);

        return new SuccessResponse(data: LocationResource::collection($result['locations']));
    }
}
