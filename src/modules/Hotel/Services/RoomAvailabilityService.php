<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;
use Modules\Hotel\Dto\RoomCalendarData;
use Modules\Hotel\Dto\StoreRoomAvailabilityData;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomDate;

class RoomAvailabilityService
{
    /**
     * @throws ForbiddenException
     */
    public function getRooms(User $user): array
    {
        $hotel = $this->resolveHotel($user);

        $rooms = HotelRoom::query()
            ->where('parent_id', $hotel->id)
            ->orderByDesc('id')
            ->get(['id', 'title', 'number', 'price', 'status', 'image_id', 'updated_at']);

        return [
            'hotel_id' => $hotel->id,
            'rooms' => $rooms,
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function loadDates(RoomCalendarData $dto, User $user): array
    {
        $hotel = $this->resolveHotel($user);

        if ($dto->id === 'summary') {
            return $this->getSummaryCalendar($hotel->id, $dto);
        }

        $room = HotelRoom::query()->find($dto->id);
        if (!$room || (int) $room->parent_id !== (int) $hotel->id) {
            throw new NotFoundException(
                errorCode: 'room_not_found',
                domain: 'hotel',
            );
        }

        return $this->getRoomCalendar($room, $dto);
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function storeDates(
        HotelRoom $room,
        StoreRoomAvailabilityData $data,
        User $user,
    ): array {
        $hotel = $this->resolveHotel($user);
        $this->assertRoomBelongsToHotel($room, $hotel);

        $updatedCount = DB::transaction(function () use ($room, $data): int {
            $count = 0;
            $period = periodDate($data->startDate, $data->endDate);

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');

                if ($data->dayOfWeekSelect !== []
                    && !in_array((int) date('N', strtotime($dateKey)), $data->dayOfWeekSelect, true)
                ) {
                    continue;
                }

                $row = HotelRoomDate::query()
                    ->where('target_id', $room->id)
                    ->whereDate('start_date', $dateKey)
                    ->first();

                if (!$row) {
                    $row = new HotelRoomDate();
                    $row->target_id = $room->id;
                }

                $row->start_date = $dateKey . ' 00:00:00';
                $row->end_date = $dateKey . ' 00:00:00';
                $row->price = $data->price ?? $room->price;
                $row->number = $data->number;
                $row->active = $data->active ? 1 : 0;
                $row->is_instant = $data->isInstant ? 1 : 0;
                $row->save();

                $count++;
            }

            return $count;
        });

        return [
            'code' => 'room_availability_updated',
            'data' => [
                'room_id' => $room->id,
                'updated_days' => $updatedCount,
            ],
        ];
    }

    public function getRoomCalendar(HotelRoom $room, RoomCalendarData $data): array
    {
        $rows = HotelRoomDate::query()
            ->where('target_id', $room->id)
            ->whereBetween('start_date', [
                date('Y-m-d 00:00:00', strtotime($data->start)),
                date('Y-m-d 23:59:59', strtotime($data->end)),
            ])
            ->get()
            ->keyBy(fn ($row) => date('Y-m-d', strtotime($row->start_date)));

        $allDates = [];
        $period = periodDate($data->start, $data->end, false);

        foreach ($period as $dt) {
            $dateKey = $dt->format('Y-m-d');

            $allDates[$dateKey] = [
                'id' => uniqid(),
                'start' => $dateKey,
                'allDay' => true,
                'price' => $room->price,
                'number' => $room->number,
                'active' => 1,
                'extendedProps' => [
                    'max_number' => $room->number,
                ],
            ];

            $priceHtml = $data->forSingle ? format_money($room->price) : format_money_main($room->price);
            $allDates[$dateKey]['title'] = $priceHtml . ' x ' . $room->number;
        }

        foreach ($rows as $dateKey => $row) {
            $price = $row->price ?: $room->price;
            $number = ($row->number !== null) ? (int) $row->number : $room->number;

            $existing = $allDates[$dateKey];

            $isActive = (int) $row->active;
            $priceChanged = false;
            $numberChanged = false;

            if ($isActive) {
                $priceChanged = $row->price !== null && abs((float) $row->price - (float) $room->price) > 0.01;
                $numberChanged = $row->number !== null && (int) $row->number != (int) $room->number;
            }

            if (!$isActive) {
                $title = __('hotel.calendar.blocked');
            } elseif ($number == 0) {
                $title = __('hotel.calendar.full_books');
            } else {
                $title = format_money_main($price) . ' x ' . $number;
            }

            $allDates[$dateKey] = array_merge(
                $existing,
                [
                    'price' => $price,
                    'number' => $number,
                    'active' => $isActive,
                    'title' => $title,
                ],
                [
                    'extendedProps' => array_merge(
                        $existing['extendedProps'],
                        [
                            'max_number' => $room->number,
                            'price_changed' => $priceChanged,
                            'number_changed' => $numberChanged,
                        ]
                    ),
                ]
            );
        }

        $bookings = $room->getBookingsInRange($data->start, $data->end);

        foreach ($bookings as $roomBooking) {
            $booking = Booking::find($roomBooking->booking_id);
            if (!$booking) {
                continue;
            }

            $bookingPeriod = periodDate(
                $roomBooking->start_date,
                $roomBooking->end_date,
                false
            );

            $endDate = Carbon::parse($roomBooking->end_date)->format('Y-m-d');

            foreach ($bookingPeriod as $dt) {
                $dateKey = $dt->format('Y-m-d');
                if (!isset($allDates[$dateKey])) {
                    continue;
                }

                $day = &$allDates[$dateKey];
                $isCheckout = $dateKey === $endDate;

                $day['bookings'][] = [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'code' => $booking->code,
                    'status' => $booking->status,
                    'statusName' => $booking->statusName,
                    'is_checkout' => $isCheckout,
                ];

                if (!$isCheckout) {
                    $bookedRooms = (int) ($roomBooking->number ?? 0);
                    $day['occupiedRooms'] = ($day['occupiedRooms'] ?? 0) + $bookedRooms;

                    $baseNumber = $day['extendedProps']['max_number'];
                    $freeRooms = max($baseNumber - ($day['occupiedRooms'] ?? 0), 0);

                    if ($freeRooms <= 0) {
                        $day['active'] = 1;
                        $day['number'] = 0;
                        $day['title'] = __('hotel.calendar.full_books');
                    } else {
                        $day['active'] = 1;
                        $day['number'] = $freeRooms;
                        $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                    }
                } else {
                    $day['is_checkout_day'] = true;
                    $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                }
            }
        }

        return array_values($allDates);
    }

    public function getSummaryCalendar(int $hotelId, RoomCalendarData $data): array
    {
        $rooms = HotelRoom::query()
            ->where('parent_id', $hotelId)
            ->get();

        $allDates = [];
        $period = periodDate($data->start, $data->end, false);

        foreach ($period as $dt) {
            $dateKey = $dt->format('Y-m-d');
            $allDates[$dateKey] = [
                'id' => uniqid(),
                'start' => $dateKey,
                'allDay' => true,
                'price' => 0,
                'number' => 0,
                'active' => 1,
                'extendedProps' => [
                    'max_number' => 0,
                    'price_changed' => false,
                    'number_changed' => false,
                    'is_summary' => true,
                ],
                'title' => '',
                'bookings' => [],
            ];
        }

        foreach ($rooms as $room) {
            $customDates = HotelRoomDate::query()
                ->where('target_id', $room->id)
                ->whereBetween('start_date', [
                    date('Y-m-d 00:00:00', strtotime($data->start)),
                    date('Y-m-d 23:59:59', strtotime($data->end)),
                ])
                ->get()
                ->keyBy(fn ($row) => date('Y-m-d', strtotime($row->start_date)));

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                $day = &$allDates[$dateKey];
                $price = $room->price;
                $number = $room->number;

                if (isset($customDates[$dateKey])) {
                    $row = $customDates[$dateKey];
                    $price = $row->price ?: $price;
                    $number = $row->number !== null ? (int) $row->number : $number;
                }

                $day['number'] += $number;
                $day['extendedProps']['max_number'] += $room->number;
                $day['price'] = $day['price'] ?: $price;

                $priceHtml = $data->forSingle ? format_money($day['price']) : format_money_main($day['price']);
                $day['title'] = $priceHtml . ' x ' . $day['number'];

                $roomBookings = $room->getBookingsInRange($data->start, $data->end);
                foreach ($roomBookings as $rb) {
                    $booking = Booking::find($rb->booking_id);
                    if (!$booking) {
                        continue;
                    }

                    $bookingStart = Carbon::parse($rb->start_date)->format('Y-m-d');
                    $bookingEnd = Carbon::parse($rb->end_date)->format('Y-m-d');

                    if ($dateKey >= $bookingStart && $dateKey <= $bookingEnd) {
                        $isCheckout = $dateKey === $bookingEnd;
                        $day['bookings'][$booking->id] = [
                            'id' => $booking->id,
                            'booking_number' => $booking->booking_number,
                            'code' => $booking->code,
                            'status' => $booking->status,
                            'statusName' => $booking->statusName,
                            'is_checkout' => $isCheckout,
                        ];
                        if ($isCheckout) {
                            $day['is_checkout_day'] = true;
                        }
                    }
                }
            }
            unset($day);
        }

        foreach ($allDates as &$day) {
            if (!empty($day['bookings'])) {
                $day['bookings'] = array_values($day['bookings']);
            }
        }
        unset($day);

        return array_values($allDates);
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
