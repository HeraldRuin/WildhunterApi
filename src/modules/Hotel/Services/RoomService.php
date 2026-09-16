<?php

namespace Modules\Hotel\Services;

use Modules\Hotel\Dto\CalendarAvailabilityData;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomBooking;
use Modules\Hotel\Models\HotelRoomDate;

class RoomService
{
    public function getAvailableRooms(Hotel $hotel, array $filters = []): array
    {
        $startDate = $filters['check_in'] ?? $filters['start_date'] ?? null;
        $endDate = $filters['check_out'] ?? $filters['end_date'] ?? null;
        $hasDates = !empty($startDate) && !empty($endDate);

        $dateFilters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $rooms = [];

        foreach ($hotel->rooms as $room) {
            if ($hasDates) {
                if (!$room->isAvailableAt($dateFilters)) {
                    continue;
                }

                $availableRooms = (int) ($room->tmp_number ?? 0);
                $price = (float) ($room->tmp_price ?? 0);
                $nights = (int) ($room->tmp_nights ?? 0);
            } else {
                $availableRooms = (int) ($room->number ?? 0);
                $price = (float) ($room->price ?? 0);
                $nights = 1;
            }

            if ($availableRooms <= 0) {
                continue;
            }

            $roomAdults = (int) ($room->adults ?? 0);
            if ($roomAdults <= 0) {
                continue;
            }

            $room->setAttribute('available_number', $availableRooms);
            $room->setAttribute('calculated_price', $price);
            $room->setAttribute('calculated_nights', $nights);
            $room->setAttribute('number_selected', 0);
            $rooms[] = $room;
        }

        $requestedAdults = (int) ($filters['adults'] ?? 0);
        if ($requestedAdults > 0 && $hasDates) {
            $totalCapacity = array_sum(array_map(
                fn (HotelRoom $room) => $room->adults * $room->available_number,
                $rooms
            ));

            if ($totalCapacity < $requestedAdults) {
                return [];
            }
        }

        return $rooms;
    }

    /**
     * @return list<array{date: string, available_rooms: int}>
     */
    public function getCalendarAvailabilityDays(Hotel $hotel, CalendarAvailabilityData $dto): array
    {
        $rooms = $hotel->rooms;

        if ($dto->adults !== null) {
            $rooms = $rooms->filter(
                fn (HotelRoom $room) => (int) $room->adults >= $dto->adults
            );
        }

        $days = [];
        $period = periodDate($dto->start, $dto->end, false);

        foreach ($period as $dt) {
            $days[$dt->format('Y-m-d')] = 0;
        }

        if ($rooms->isEmpty() || $days === []) {
            return $this->formatCalendarDays($days);
        }

        $roomIds = $rooms->pluck('id')->all();

        $customDates = HotelRoomDate::query()
            ->whereIn('target_id', $roomIds)
            ->where('start_date', '<=', $dto->end . ' 23:59:59')
            ->where('end_date', '>=', $dto->start . ' 00:00:00')
            ->get()
            ->groupBy('target_id');

        $bookings = HotelRoomBooking::query()
            ->whereIn('bc_hotel_room_bookings.room_id', $roomIds)
            ->active()
            ->inRange($dto->start, $dto->end)
            ->get(['bc_hotel_room_bookings.*'])
            ->groupBy('room_id');

        foreach ($rooms as $room) {
            $datesByDay = ($customDates->get($room->id) ?? collect())
                ->keyBy(fn ($row) => date('Y-m-d', strtotime($row->start_date)));
            $roomBookings = $bookings->get($room->id) ?? collect();

            foreach ($days as $date => $_) {
                $inventory = (int) $room->number;

                if (isset($datesByDay[$date])) {
                    $row = $datesByDay[$date];
                    if (!(int) $row->active) {
                        continue;
                    }
                    if ($row->number !== null) {
                        $inventory = (int) $row->number;
                    }
                }

                $occupied = 0;
                foreach ($roomBookings as $booking) {
                    $bookingStart = date('Y-m-d', strtotime($booking->start_date));
                    $bookingEnd = date('Y-m-d', strtotime($booking->end_date));

                    if ($date >= $bookingStart && $date < $bookingEnd) {
                        $occupied += (int) $booking->number;
                    }
                }

                $days[$date] += max($inventory - $occupied, 0);
            }
        }

        return $this->formatCalendarDays($days);
    }

    /**
     * @param array<string, int> $days
     * @return list<array{date: string, available_rooms: int}>
     */
    private function formatCalendarDays(array $days): array
    {
        $result = [];

        foreach ($days as $date => $availableRooms) {
            $result[] = [
                'date' => $date,
                'available_rooms' => $availableRooms,
            ];
        }

        return $result;
    }
}
