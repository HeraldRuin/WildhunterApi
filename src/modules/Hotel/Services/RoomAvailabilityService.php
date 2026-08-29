<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Dto\RoomCalendarData;
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
                    'classNames' => $isActive ? ['available-event'] : ['blocked-event'],
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

                $day['bookings'][] = [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'code' => $booking->code,
                    'status' => $booking->status,
                    'statusName' => $booking->statusName,
                ];

                if ($dateKey !== $endDate) {
                    $bookedRooms = (int) ($roomBooking->number ?? 0);
                    $day['occupiedRooms'] = ($day['occupiedRooms'] ?? 0) + $bookedRooms;

                    $baseNumber = $day['extendedProps']['max_number'];
                    $freeRooms = max($baseNumber - ($day['occupiedRooms'] ?? 0), 0);

                    if ($freeRooms <= 0) {
                        $day['active'] = 1;
                        $day['number'] = 0;
                        $day['classNames'] = ['full-book-event'];
                        $day['title'] = __('hotel.calendar.full_books');
                    } else {
                        $day['active'] = 1;
                        $day['number'] = $freeRooms;
                        $day['classNames'] = ['available-event'];
                        $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                    }
                } else {
                    $day['classNames'] = ['checkout-day-event'];
                    $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                }
            }
        }

        foreach ($allDates as &$day) {
            if (empty($day['bookings'])) {
                continue;
            }

            $bookingHtml = '<div class="calendar-bookings">';
            foreach ($day['bookings'] as $b) {
                $status = htmlspecialchars($b['status'] ?? '');
                $label = htmlspecialchars($b['statusName'] ?? '');

                $bookingHtml .= '<div class="booking-item booking-status-' . $status . '">'
                    . '<span class="booking-id" data-id="' . (int) $b['id'] . '" data-code="' . e($b['code']) . '">'
                    . 'Б' . htmlspecialchars((string) $b['booking_number']) .
                    '</span>'
                    . '<span class="booking-status">' . $label . '</span>';

                if ($this->isCheckoutDay((int) $b['id'], $day['start'])) {
                    $bookingHtml .= ' <span class="checkout-label">(В)</span>';
                }
                $bookingHtml .= '</div>';
            }

            $bookingHtml .= '</div>';
            $day['bookings_html'] = $bookingHtml;
        }
        unset($day);

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
                'bookings_html' => '',
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
                        $day['bookings'][$booking->id] = [
                            'id' => $booking->id,
                            'booking_number' => $booking->booking_number,
                            'code' => $booking->code,
                            'status' => $booking->status,
                            'statusName' => $booking->statusName,
                        ];
                    }
                }
            }
            unset($day);
        }

        foreach ($allDates as &$day) {
            if (empty($day['bookings'])) {
                continue;
            }

            $day['bookings'] = array_values($day['bookings']);

            $bookingHtml = '<div class="calendar-bookings">';
            foreach ($day['bookings'] as $b) {
                $code = htmlspecialchars($b['code'] ?? '');

                $bookingHtml .= '<div class="booking-item">'
                    . '<span class="booking-id" data-id="' . (int) $b['id'] . '" data-code="' . $code . '">'
                    . 'Б' . htmlspecialchars((string) $b['booking_number']) .
                    '</span>';

                if ($this->isCheckoutDay((int) $b['id'], $day['start'])) {
                    $bookingHtml .= ' <span class="checkout-label">(В)</span>';
                }

                $bookingHtml .= '</div>';
            }
            $bookingHtml .= '</div>';
            $day['bookings_html'] = $bookingHtml;
        }
        unset($day);

        return array_values($allDates);
    }

    protected function isCheckoutDay(int $bookingId, string $date): bool
    {
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return false;
        }

        $endDate = Carbon::parse($booking->end_date)->format('Y-m-d');

        return $endDate === $date;
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
}
