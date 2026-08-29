<?php

namespace Modules\Hotel\Controllers;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Services\ManageRoomService;

class ManageRoomController extends Controller
{
    public function __construct(
        private readonly ManageRoomService $manageRoomService,
    ) {
    }

    /**
     * Опубликовать номер (status = publish).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function publish(HotelRoom $room): JsonResponse
    {
        $result = $this->manageRoomService->publish($room, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'hotel',
            data: $result['data'],
        );
    }

    /**
     * Скрыть номер (status = draft).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function hide(HotelRoom $room): JsonResponse
    {
        $result = $this->manageRoomService->hide($room, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'hotel',
            data: $result['data'],
        );
    }

    /**
     * Удалить номер (soft delete).
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function destroy(HotelRoom $room): JsonResponse
    {
        $result = $this->manageRoomService->delete($room, Auth::user());

        return new SuccessResponse(
            code: $result['code'],
            domain: 'hotel',
            data: $result['data'],
        );
    }
}
