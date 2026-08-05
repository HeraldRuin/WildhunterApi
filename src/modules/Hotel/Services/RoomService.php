<?php

namespace Modules\Hotel\Services;

use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;

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
}
