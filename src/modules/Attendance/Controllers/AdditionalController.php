<?php

namespace Modules\Attendance\Controllers;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Dto\StoreAdditionalData;
use Modules\Attendance\Dto\UpdateAdditionalData;
use Modules\Attendance\Http\Requests\StoreAdditionalRequest;
use Modules\Attendance\Http\Requests\UpdateAdditionalRequest;
use Modules\Attendance\Http\Resources\AdditionalResource;
use Modules\Attendance\Http\Resources\SystemServiceResource;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Attendance\Services\AdditionalService;

class AdditionalController extends Controller
{
    public function __construct(
        private readonly AdditionalService $additionalService,
    ) {
    }

    /**
     * @throws ForbiddenException
     */
    public function index(): JsonResponse
    {
        $additionals = $this->additionalService->list(Auth::user());

        return new SuccessResponse(data: AdditionalResource::collection($additionals));
    }

    /**
     * @throws ForbiddenException
     */
    public function systemIndex(): JsonResponse
    {
        $services = $this->additionalService->listSystem(Auth::user());

        return new SuccessResponse(data: SystemServiceResource::collection($services));
    }

    /**
     * @throws ForbiddenException
     */
    public function store(StoreAdditionalRequest $request): JsonResponse
    {
        $data = StoreAdditionalData::fromRequest($request);
        $result = $this->additionalService->store($data, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'additional',
            data: [
                'additional' => (new AdditionalResource($result['data']['additional']))->resolve(),
            ],
        );
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function update(UpdateAdditionalRequest $request, AddetionalPrice $additional): JsonResponse
    {
        $data = UpdateAdditionalData::fromRequest($request);
        $result = $this->additionalService->update($additional, $data, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'additional',
            data: [
                'additional' => (new AdditionalResource($result['data']['additional']))->resolve(),
            ],
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function destroy(AddetionalPrice $additional): JsonResponse
    {
        $result = $this->additionalService->delete($additional, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'additional',
            data: $result['data'],
        );
    }
}
