<?php

namespace Modules\Hotel\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Hotel\Dto\RoomCalendarData;
use Modules\Hotel\Dto\StoreRoomAvailabilityData;
use Modules\Hotel\Http\Request\LoadRoomAvailabilityRequest;
use Modules\Hotel\Http\Request\StoreRoomAvailabilityRequest;
use Modules\Hotel\Http\Resources\RoomCalendarListResource;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Services\RoomAvailabilityService;

class RoomAvailabilityController extends Controller
{
    public function __construct(
        private readonly RoomAvailabilityService $roomAvailabilityService,
    ) {
    }

    /**
     * Список номеров отеля для вкладок календаря (аналог index в booking_core).
     *
     * @throws ForbiddenException
     */
    public function index(): JsonResponse
    {
        $result = $this->roomAvailabilityService->getRooms(Auth::user());

        return new SuccessResponse(data: [
            'hotel_id' => $result['hotel_id'],
            'rooms' => RoomCalendarListResource::collection($result['rooms'])->resolve(),
        ]);
    }

    /**
     * Дни календаря доступности (аналог loadDates в booking_core).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function loadDates(LoadRoomAvailabilityRequest $request): JsonResponse
    {
        $dto = RoomCalendarData::fromRequest($request);
        $dates = $this->roomAvailabilityService->loadDates($dto, Auth::user());

        return new SuccessResponse(data: $dates);
    }

    /**
     * Сохранение параметров доступности по дням (аналог store в booking_core).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function store(
        StoreRoomAvailabilityRequest $request,
        HotelRoom $room,
    ): JsonResponse {
        $dto = StoreRoomAvailabilityData::fromRequest($request);
        $result = $this->roomAvailabilityService->storeDates($room, $dto, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'hotel',
            data: $result['data'],
        );
    }
}
