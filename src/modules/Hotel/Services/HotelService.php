<?php

namespace Modules\Hotel\Services;

use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Dto\HotelFilterData;
use Modules\Hotel\Dto\HotelSearchData;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Hotel\Models\HotelRoomDate;

class HotelService
{
    public function __construct(private HotelSearchService $searchService)
    {
    }

    public function getHotels(HotelFilterData $dto): array
    {
        $hotels = Hotel::published()
            ->when($dto->order_by, function ($q) use ($dto) {
                $q->orderBy($dto->order_by, $dto->order_direction ?? 'asc');
            })
            ->when($dto->limit, fn($q) => $q->limit($dto->limit))
            ->get();

        return [
            'code' => '',
            'data' => $hotels
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getHotel($hotelId): array
    {
        $hotel = Hotel::published()->find($hotelId);

        if (!$hotel) {
            throw new ValidationException(
                errorCode: 'hotel_not_found',
                domain: 'hotel'
            );
        }

        return [
            'code' => '',
            'data' => $hotel
        ];
    }

    public function searchHotels(HotelSearchData $dto): array
    {
        if(!empty($dto->limit)){
            $limit = $dto->limit;
        }else{
            $limit = !empty(setting_item("hotel_page_limit_item"))? setting_item("hotel_page_limit_item") : 9;
        }

        if ($dto->startDate && $dto->endDate) {
            $start = Carbon::parse($dto->startDate)->startOfDay();
            $end   = Carbon::parse($dto->endDate)->endOfDay();

            $query = $this->searchService->search($dto);
            $hotelsCollection = collect($query->get());

            $hotelsCollection = $this->filterHotelsByAvailability($hotelsCollection, $start, $end);

            $guestCount = $dto->adults;

            if ($guestCount > 0) {
                $hotelsCollection = $this->filterHotelsByGuestCountAndAvailability($hotelsCollection, $guestCount, $start, $end);
            }
        } else {
            $hotelsCollection = collect(Hotel::query()->get());
        }


        $perPage = $limit;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $list = new LengthAwarePaginator(
            $hotelsCollection->forPage($currentPage, $perPage),
            $hotelsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        $hotels = [
            'rows' => $list,
//            'markers'            => $markers,
        ];

        return [
            'code' => '',
            'data' => $hotels['rows']
        ];
    }
    public function filterHotelsByAvailability($hotels, Carbon $start, Carbon $end)
    {
        return $hotels->filter(/**
         * @throws \Exception
         */ function ($hotel) use ($start, $end) {

            foreach ($hotel->rooms as $room) {

                $period = CarbonPeriod::create($start, $end);
                $isRoomAvailable = true;

                $customDates = HotelRoomDate::query()
                    ->where('target_id', $room->id)
                    ->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->get()
                    ->keyBy(fn($row) => (new \Carbon\Carbon($row->start_date))->toDateString());

                $bookings = $room->getBookingsInRange($start, $end);

                foreach ($period as $date) {
                    $dateKey = $date->format('Y-m-d');

                    $baseNumber = $room->number;
                    if (isset($customDates[$dateKey]) && $customDates[$dateKey]->number !== null) {
                        $baseNumber = (int)$customDates[$dateKey]->number;
                    }

                    $occupied = 0;
                    foreach ($bookings as $booking) {
                        $bookingPeriod = periodDate($booking->start_date, Carbon::parse($booking->end_date)->subDay(), false);
                        foreach ($bookingPeriod as $bDate) {
                            if ($bDate->format('Y-m-d') === $dateKey) {
                                $occupied += $booking->number;
                            }
                        }
                    }

                    $freeRooms = max($baseNumber - $occupied, 0);
                    if ($freeRooms <= 0) {
                        $isRoomAvailable = false;
                        break;
                    }
                }

                if ($isRoomAvailable) {
                    return true;
                }
            }

            return false;
        });
    }
    protected function filterHotelsByGuestCountAndAvailability($hotels, int $guestCount, Carbon $start, Carbon $end)
    {
        $periodStart = $start->copy()->startOfDay();
        $periodEnd   = $end->copy()->subDay()->startOfDay();
        $periodDates = [];

        for ($date = $periodStart->copy(); $date <= $periodEnd; $date->addDay()) {
            $periodDates[] = $date->format('Y-m-d');
        }

        return $hotels->filter(function ($hotel) use ($guestCount, $periodDates, $periodStart, $periodEnd) {

            $totalCapacity = 0;

            foreach ($hotel->rooms as $room) {
                $roomDates = DB::table('bc_hotel_room_dates')
                    ->where('target_id', $room->id)
                    ->whereIn(DB::raw('DATE(start_date)'), $periodDates)
                    ->get()
                    ->keyBy(function ($item) {
                        return date('Y-m-d', strtotime($item->start_date));
                    });

                $bookings = $room->getBookingsInRange($periodStart, $periodEnd);

                $dailyAvailable = [];

                foreach ($periodDates as $date) {
                    if (isset($roomDates[$date])) {
                        $number = (int)$roomDates[$date]->active ? (int)$roomDates[$date]->number : 0;
                    } else {
                        $number = (int)$room->number;
                    }

                    $occupied = 0;
                    foreach ($bookings as $booking) {
                        $bookingStart = Carbon::parse($booking->start_date)->format('Y-m-d');
                        $bookingEnd   = Carbon::parse($booking->end_date)->subDay()->format('Y-m-d');
                        if ($date >= $bookingStart && $date <= $bookingEnd) {
                            $occupied += $booking->number;
                        }
                    }

                    $freeRooms = max($number - $occupied, 0);
                    $dailyAvailable[] = $freeRooms;
                }

                $minRooms = !empty($dailyAvailable) ? min($dailyAvailable) : 0;
                $totalCapacity += $minRooms * $room->adults;
            }
            return $totalCapacity >= $guestCount;
        });
    }
}
