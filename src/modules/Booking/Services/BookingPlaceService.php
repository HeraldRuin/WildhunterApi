<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Dto\SelectPlaceData;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Hotel\Models\HotelRoom;

class BookingPlaceService
{
    /** Статусы как у available_actions.select_place */
    private const array PLACE_STATUSES = [
        Booking::FINISHED_PREPAYMENT,
        Booking::BED_COLLECTION,
        Booking::FINISHED_BED,
    ];

    /** Если все оплатили, но этап коек ещё не стартовал — можно перевести */
    private const array PRE_BED_STATUSES = [
        Booking::FINISHED_COLLECTION,
        Booking::PREPAYMENT_COLLECTION,
        Booking::FINISHED_PREPAYMENT,
    ];

    public function __construct(
        protected BookingCollectionService $bookingCollectionService,
    ) {}

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function getPlaces(string $code, User $user): array
    {
        $booking = $this->findBookingForPlaces($code, $user, self::PLACE_STATUSES);

        $rooms = $booking
            ->roomsBooking()
            ->with('room', 'booking:id,total_guests')
            ->get()
            ->map(function ($roomBooking) {
                $room = $roomBooking->room;
                $placesPerRoom = $this->placesPerRoom($room);

                return [
                    'booking_total_guests' => (int) $roomBooking->booking->total_guests,
                    'booking_room_id' => $roomBooking->id,
                    'booking_number' => (int) $roomBooking->number,
                    'room_id' => $room->id,
                    'title' => $room->title,
                    'number' => $placesPerRoom,
                    'beds' => (int) ($room->beds ?? 0),
                    'adults' => (int) ($room->adults ?? 0),
                    'total_guests_in_type' => (int) $roomBooking->number * $placesPerRoom,
                ];
            })
            ->values()
            ->all();

        $places = BookingRoomPlace::query()
            ->with('user:id,first_name,last_name,user_name')
            ->where('booking_id', $booking->id)
            ->get()
            ->groupBy(['room_index', 'room_id', 'place_number'])
            ->toArray();

        return [
            'rooms' => $rooms,
            'places' => $places,
            'is_all_places_assigned' => (bool) $booking->is_all_places_assigned,
            'status' => $booking->status,
        ];
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws ConflictException
     */
    public function selectPlace(string $code, SelectPlaceData $data, User $user): array
    {
        return DB::transaction(function () use ($code, $data, $user) {
            $booking = $this->findBookingForPlaces($code, $user, self::PLACE_STATUSES, lock: true);

            if ($booking->is_all_places_assigned) {
                throw new ForbiddenException(
                    errorCode: 'places_already_assigned',
                    domain: 'booking',
                );
            }

            $alreadyHasPlace = BookingRoomPlace::query()
                ->where('booking_id', $booking->id)
                ->where('user_id', $data->userId)
                ->lockForUpdate()
                ->exists();

            if ($alreadyHasPlace) {
                throw new ForbiddenException(
                    errorCode: 'cannot_select_more_than_one_place',
                    domain: 'booking',
                );
            }

            $roomBooking = $booking->roomsBooking()
                ->with('room')
                ->where('room_id', $data->roomId)
                ->first();

            if (!$roomBooking || !$roomBooking->room) {
                throw new NotFoundException(
                    errorCode: 'room_not_found',
                    domain: 'booking',
                );
            }

            $placesPerRoom = $this->placesPerRoom($roomBooking->room);
            $roomsCount = (int) $roomBooking->number;

            if ($data->roomIndex < 1 || $data->roomIndex > $roomsCount) {
                throw new ConflictException(
                    errorCode: 'invalid_room_index',
                    domain: 'booking',
                );
            }

            // Как в bc-cms: клик по месту 2/3/4 — сажаем на первое свободное в этом экземпляре комнаты
            $occupiedPlaceNumbers = BookingRoomPlace::query()
                ->where('booking_id', $booking->id)
                ->where('room_id', $data->roomId)
                ->where('room_index', $data->roomIndex)
                ->lockForUpdate()
                ->pluck('place_number')
                ->map(fn ($n) => (int) $n)
                ->all();

            $finalPlaceNumber = null;
            for ($i = 1; $i <= $placesPerRoom; $i++) {
                if (!in_array($i, $occupiedPlaceNumbers, true)) {
                    $finalPlaceNumber = $i;
                    break;
                }
            }

            if ($finalPlaceNumber === null) {
                throw new ConflictException(
                    errorCode: 'no_free_places_in_room',
                    domain: 'booking',
                );
            }

            BookingRoomPlace::create([
                'booking_id' => $booking->id,
                'room_index' => $data->roomIndex,
                'room_id' => $data->roomId,
                'place_number' => $finalPlaceNumber,
                'user_id' => $data->userId,
            ]);

            $this->updateStatusIfAllPlacesSelected($booking);

            return [
                'code' => 'place_selected',
            ];
        });
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function cancelSelectPlace(string $code, int $placeId, User $user): array
    {
        $booking = $this->findBookingForPlaces($code, $user, self::PLACE_STATUSES);

        if ($booking->is_all_places_assigned) {
            throw new ForbiddenException(
                errorCode: 'places_already_assigned',
                domain: 'booking',
            );
        }

        $place = BookingRoomPlace::query()
            ->where('booking_id', $booking->id)
            ->where('id', $placeId)
            ->where('user_id', $user->id)
            ->first();

        if (!$place) {
            throw new ForbiddenException(
                errorCode: 'cancel_only_own_place',
                domain: 'booking',
            );
        }

        $place->delete();

        return [
            'code' => 'place_cancelled',
        ];
    }

    public function updateStatusIfAllPlacesSelected(Booking $booking): void
    {
        $paidCount = $booking->countAcceptedAndPaidHunters();
        $placesCount = BookingRoomPlace::query()
            ->where('booking_id', $booking->id)
            ->count();

        if ($paidCount > 0 && $placesCount === $paidCount) {
            $booking->status = Booking::FINISHED_BED;
            $booking->save();
            BookingUpdatedEvent::dispatchSafely($booking);
        }
    }

    /**
     * Вместимость одного экземпляра комнаты — число койко-мест.
     * Берём adults (как автораздача), иначе beds.
     */
    private function placesPerRoom(HotelRoom $room): int
    {
        $adults = (int) ($room->adults ?? 0);
        if ($adults > 0) {
            return $adults;
        }

        $beds = (int) ($room->beds ?? 0);
        if ($beds > 0) {
            return $beds;
        }

        return 1;
    }

    /**
     * @param  list<string>  $allowedStatuses
     *
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    private function findBookingForPlaces(
        string $code,
        User $user,
        array $allowedStatuses,
        bool $lock = false,
    ): Booking {
        $query = Booking::query()->where('code', $code);

        if ($lock) {
            $query->lockForUpdate();
        }

        $booking = $query->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $this->ensureAcceptedParticipant($booking, $user);
        $this->ensureBedStageStarted($booking);

        if (!in_array($booking->status, $allowedStatuses, true)) {
            throw new ForbiddenException(
                errorCode: 'places_selection_not_available',
                domain: 'booking',
            );
        }

        return $booking;
    }

    /**
     * Если предоплата собрана, а статус ещё «сбор» / предоплата — запускаем этап коек.
     */
    private function ensureBedStageStarted(Booking $booking): void
    {
        if (in_array($booking->status, self::PLACE_STATUSES, true)) {
            return;
        }

        if (!in_array($booking->status, self::PRE_BED_STATUSES, true)) {
            return;
        }

        $accepted = $booking->countAcceptedHunters();
        $paid = $booking->countAcceptedAndPaidHunters();

        if ($accepted <= 0 || $paid < $accepted) {
            return;
        }

        $this->bookingCollectionService->startBedTimer($booking);
        $booking->refresh();
    }

    /**
     * @throws ForbiddenException
     */
    private function ensureAcceptedParticipant(Booking $booking, User $user): void
    {
        $invitation = BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($query) use ($booking) {
                $query->where('booking_id', $booking->id)
                    ->whereNull('deleted_at');
            })
            ->where('hunter_id', $user->id)
            ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
            ->whereNull('deleted_at')
            ->first();

        if (!$invitation) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }
    }
}
