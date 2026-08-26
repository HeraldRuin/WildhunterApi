<?php

namespace Modules\Booking\Controllers;

use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Dto\StoreCollectionTimerData;
use Modules\Booking\Http\Requests\ShowCollectionTimerRequest;
use Modules\Booking\Http\Requests\StoreCollectionTimerRequest;
use Modules\Booking\Services\CollectionTimerSettingsService;

class CollectionTimerController extends Controller
{
    public function __construct(
        private readonly CollectionTimerSettingsService $collectionTimerSettingsService,
    ) {
    }

    /**
     * @throws ForbiddenException
     */
    public function show(ShowCollectionTimerRequest $request): JsonResponse
    {
        $result = $this->collectionTimerSettingsService->get(
            $request->validated('type'),
            Auth::user(),
        );

        return new SuccessResponse(data: $result);
    }

    /**
     * @throws ForbiddenException
     */
    public function store(StoreCollectionTimerRequest $request): JsonResponse
    {
        $result = $this->collectionTimerSettingsService->save(
            StoreCollectionTimerData::fromRequest($request),
            Auth::user(),
        );

        return new SuccessResponse(
            code: 'timer_settings_saved',
            domain: 'collection',
            data: $result,
        );
    }
}
