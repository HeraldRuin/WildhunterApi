<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Modules\Booking\Events\BookingHistoryUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingBedAllocatorService
{
    public function areAllHuntersAssigned(Booking $booking)
    {
        $masterBookingHunterIds = $booking->bookingHunter()
            ->where('is_master', true)
            ->pluck('id');

        return BookingHunterInvitation::whereIn('booking_hunter_id', $masterBookingHunterIds)
            ->where('status', 'accepted')
            ->where('prepayment_paid', true)
            ->pluck('hunter_id');
    }

    /**
     * Основной метод для распределения охотников по койкам
     */
    public function allocateBeds(Booking $booking): void
    {
        $invitedHunters = $this->areAllHuntersAssigned($booking);

        $places = BookingRoomPlace::whereIn('user_id', $invitedHunters)
            ->where('booking_id',$booking->id)
            ->get();

        if ($invitedHunters->count() === $places->count()) {
            return;
        }

        $this->assignRemainingHunters($booking, $invitedHunters, $places);
    }

    protected function assignRemainingHunters(Booking $booking, $invitedHunters, $places): void
    {
        $notSetBedHunterIds = $invitedHunters->diff($places->pluck('user_id'));
        $this->assignToPartiallyFilledRooms($booking, $notSetBedHunterIds);
    }

    protected function assignToPartiallyFilledRooms(Booking $booking, $notSetBedHunterIds): void
    {
        if ($notSetBedHunterIds->isEmpty()) {
            return;
        }

        $huntersWithoutPlace = [];

        DB::transaction(function () use ($booking, &$notSetBedHunterIds, &$huntersWithoutPlace) {

            $roomBookings = $booking->roomsBooking()->with('room')->get();

            $roomsState = [];

            foreach ($roomBookings as $roomBooking) {

                $roomId = $roomBooking->room_id;
                $capacity = $roomBooking->room->adults;

                for ($index = 1; $index <= $roomBooking->number; $index++) {

                    $currentPlaces = DB::table('bc_booking_room_places')
                        ->where('booking_id', $booking->id)
                        ->where('room_id', $roomId)
                        ->where('room_index', $index)
                        ->count();

                    $roomsState[] = [
                        'room_id' => $roomId,
                        'room_index' => $index,
                        'capacity' => $capacity,
                        'current' => $currentPlaces
                    ];
                }
            }

            while ($notSetBedHunterIds->isNotEmpty()) {

                $placedSomeone = false;

                foreach ($roomsState as &$room) {

                    if ($notSetBedHunterIds->isEmpty()) {
                        break;
                    }

                    if ($room['current'] >= $room['capacity']) {
                        continue;
                    }

                    $hunterId = $notSetBedHunterIds->shift();

                    DB::table('bc_booking_room_places')->insert([
                        'booking_id' => $booking->id,
                        'room_id' => $room['room_id'],
                        'room_index' => $room['room_index'],
                        'place_number' => $room['current'] + 1,
                        'user_id' => $hunterId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $room['current']++;
                    $placedSomeone = true;
                }

                if (!$placedSomeone) {
                    break;
                }
            }

            if ($notSetBedHunterIds->isNotEmpty()) {
                $huntersWithoutPlace = $notSetBedHunterIds->toArray();
            }
        });

        if (!empty($huntersWithoutPlace)) {
            Log::warning('Не удалось присвоить место охотникам', [
                'booking_id' => $booking->id,
                'hunters_without_place' => $huntersWithoutPlace
            ]);
        }

        if (empty($huntersWithoutPlace)) {
            $booking->update([
                'status' => Booking::FINISHED_BED,
                'is_all_places_assigned' => true
            ]);

            BookingUpdatedEvent::dispatchSafely($booking);
            $this->notifyHistoryUpdated($booking);
        }
    }

    private function notifyHistoryUpdated(Booking $booking): void
    {
        foreach ($this->historyRecipientIds($booking) as $userId) {
            BookingHistoryUpdatedEvent::dispatchSafely(
                $booking,
                $userId,
                BookingHistoryUpdatedEvent::ACTION_UPDATED,
            );
        }
    }

    /**
     * @return list<int>
     */
    private function historyRecipientIds(Booking $booking): array
    {
        $ids = [];

        $customerId = (int) ($booking->create_user ?: $booking->customer_id);
        if ($customerId > 0) {
            $ids[] = $customerId;
        }

        if ($booking->hotel_id) {
            $adminIds = User::query()
                ->whereHas('hotels', static function ($query) use ($booking): void {
                    $query->whereKey($booking->hotel_id);
                })
                ->pluck('id');

            foreach ($adminIds as $adminId) {
                $ids[] = (int) $adminId;
            }
        }

        $masterId = (int) ($booking->masterHunter()->value('invited_by') ?? 0);
        if ($masterId > 0) {
            $ids[] = $masterId;
        }

        $hunterIds = BookingHunterInvitation::query()
            ->whereHas('bookingHunter', static function ($query) use ($booking): void {
                $query->where('booking_id', $booking->id);
            })
            ->whereNotNull('hunter_id')
            ->where(static function ($query): void {
                $query->whereNull('status')
                    ->orWhereNotIn('status', [
                        BookingHunterInvitation::STATUS_DECLINED,
                        'removed',
                    ]);
            })
            ->pluck('hunter_id');

        foreach ($hunterIds as $hunterId) {
            $ids[] = (int) $hunterId;
        }

        return array_values(array_unique(array_filter(
            $ids,
            static fn (int $id): bool => $id > 0,
        )));
    }
}

