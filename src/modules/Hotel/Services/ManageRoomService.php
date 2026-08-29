<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;

class ManageRoomService
{
    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function publish(HotelRoom $room, User $user): array
    {
        return $this->setStatus($room, $user, 'publish', 'room_published');
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function hide(HotelRoom $room, User $user): array
    {
        return $this->setStatus($room, $user, 'draft', 'room_hidden');
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete(HotelRoom $room, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertRoomBelongsToHotel($room, $hotel);

        $id = $room->id;
        $room->delete();

        return [
            'code' => 'room_deleted',
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    private function setStatus(HotelRoom $room, User $user, string $status, string $code): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertRoomBelongsToHotel($room, $hotel);

        if ($room->status !== $status) {
            $room->status = $status;
            $room->save();
        }

        return [
            'code' => $code,
            'data' => [
                'id' => $room->id,
                'status' => $room->status,
                'status_label' => __('hotel.statuses.' . $room->status),
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function resolveHotel(User $user): Hotel
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'rooms_access_denied',
                domain: 'hotel',
            );
        }

        $hotel = $user->hotels()->first();

        if (!$hotel) {
            throw new ForbiddenException(
                errorCode: 'hotel_required',
                domain: 'hotel',
            );
        }

        return $hotel;
    }

    /**
     * @throws NotFoundException
     */
    private function assertRoomBelongsToHotel(HotelRoom $room, Hotel $hotel): void
    {
        if ((int) $room->parent_id !== (int) $hotel->id) {
            throw new NotFoundException(
                errorCode: 'room_not_found',
                domain: 'hotel',
            );
        }
    }
}
