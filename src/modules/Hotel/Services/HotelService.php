<?php

namespace Modules\Hotel\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Dto\CheckAvailabilityData;
use Modules\Hotel\Dto\HotelFilterData;
use Modules\Hotel\Dto\HotelSearchData;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Hotel\Models\HotelRoomDate;
use Modules\Location\Models\Location;

class HotelService
{
    public function __construct(
        private HotelSearchService $searchService,
        private RoomService $roomService,
    ) {
    }

    public function getHotels(HotelFilterData $dto): array
    {
        $settings = $this->resolveListHotelSettings($dto);

        $query = Hotel::published()
            ->with(['location', 'reviews'])
            ->whereNotNull('location_id')
            ->where('location_id', '>', 0)
            ->whereHas('location', fn ($q) => $q->where('status', 'publish'));

        if (!empty($settings['location_id'])) {
            $location = Location::query()
                ->where('id', $settings['location_id'])
                ->where('status', 'publish')
                ->first();

            if ($location) {
                $query->whereHas('location', function ($q) use ($location) {
                    $q->where('_lft', '>=', $location->_lft)
                        ->where('_rgt', '<=', $location->_rgt);
                });
            }
        }

        if (!empty($settings['is_featured'])) {
            $query->where('is_featured', 1);
        }

        $customIds = array_values(array_filter(array_map('intval', (array) ($settings['custom_ids'] ?? []))));

        if (!empty($customIds)) {
            $query->whereIn('bc_hotels.id', $customIds);
            $query->orderByRaw(
                'FIELD(' . $query->getModel()->qualifyColumn('id') . ', ' . implode(', ', $customIds) . ') ASC'
            );
        } elseif (!empty($settings['order_by'])) {
            $query->orderBy($settings['order_by'], $settings['order_direction'] ?? 'asc');
        } else {
            $query->orderByDesc('is_featured')->orderByDesc('id');
        }

        if (!empty($settings['limit'])) {
            $query->limit((int) $settings['limit']);
        }

        return [
            'code' => '',
            'data' => $query->get(),
        ];
    }

    /**
     * @return array{
     *     is_featured: bool,
     *     custom_ids: list<int>,
     *     location_id: int|null,
     *     limit: int|null,
     *     order_by: string|null,
     *     order_direction: string|null
     * }
     */
    private function resolveListHotelSettings(HotelFilterData $dto): array
    {
        $settings = [
            'is_featured' => true,
            'custom_ids' => [],
            'location_id' => null,
            'limit' => 5,
            'order_by' => 'id',
            'order_direction' => 'desc',
        ];

        $block = $this->getHomeListHotelBlockModel();
        if (!empty($block)) {
            if (array_key_exists('is_featured', $block)) {
                $settings['is_featured'] = $this->toBool($block['is_featured']);
            }
            if (!empty($block['number'])) {
                $settings['limit'] = (int) $block['number'];
            }
            if (!empty($block['order'])) {
                $settings['order_by'] = (string) $block['order'];
            }
            if (!empty($block['order_by'])) {
                $settings['order_direction'] = strtolower((string) $block['order_by']) === 'asc' ? 'asc' : 'desc';
            }
            if (!empty($block['custom_ids'])) {
                $settings['custom_ids'] = array_values(array_filter(array_map(
                    'intval',
                    is_array($block['custom_ids']) ? $block['custom_ids'] : explode(',', (string) $block['custom_ids'])
                )));
            }
            if (!empty($block['location_id'])) {
                $settings['location_id'] = (int) $block['location_id'];
            }
        }

        if ($dto->is_featured !== null) {
            $settings['is_featured'] = $dto->is_featured;
        }
        if ($dto->custom_ids !== null) {
            $settings['custom_ids'] = $dto->custom_ids;
        }
        if ($dto->location_id !== null) {
            $settings['location_id'] = $dto->location_id;
        }
        if ($dto->limit !== null) {
            $settings['limit'] = $dto->limit;
        }
        if ($dto->order_by !== null) {
            $settings['order_by'] = $dto->order_by;
        }
        if ($dto->order_direction !== null) {
            $settings['order_direction'] = $dto->order_direction;
        }

        return $settings;
    }

    /**
     * @return array<string, mixed>
     */
    private function getHomeListHotelBlockModel(): array
    {
        $homePageId = setting_item('home_page_id');
        if (empty($homePageId)) {
            return [];
        }

        $page = DB::table('core_pages')
            ->where('id', $homePageId)
            ->whereNull('deleted_at')
            ->first();

        if (!$page || empty($page->template_id)) {
            return [];
        }

        $content = DB::table('core_templates')
            ->where('id', $page->template_id)
            ->value('content');

        if (empty($content) && Schema::hasTable('core_template_translations')) {
            $content = DB::table('core_template_translations')
                ->where('origin_id', $page->template_id)
                ->where('locale', app()->getLocale())
                ->value('content');
        }

        if (empty($content)) {
            return [];
        }

        $blocks = json_decode($content, true);
        if (!is_array($blocks)) {
            return [];
        }

        if (isset($blocks['ROOT']['nodes']) && is_array($blocks['ROOT']['nodes'])) {
            foreach ($blocks['ROOT']['nodes'] as $nodeId) {
                $block = $blocks[$nodeId] ?? null;
                if (is_array($block) && ($block['type'] ?? null) === 'list_hotel') {
                    return is_array($block['model'] ?? null) ? $block['model'] : [];
                }
            }

            return [];
        }

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }
            if (($block['type'] ?? null) === 'list_hotel') {
                return is_array($block['model'] ?? null) ? $block['model'] : [];
            }
        }

        return [];
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @throws NotFoundException
     */
    public function getHotel($location, $slug): array
    {
        $hotel = Hotel::published()->where('slug', $slug)->first();

        if (!$hotel) {
            throw new NotFoundException(
                errorCode: 'hotel_not_found',
                domain: 'hotel'
            );
        }

        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();

        $hotel->load([
            'animals' => function ($query) use ($currentMonthStart) {
                $query->where('bc_animals.status', 'publish')
                    ->wherePivot('status', 'available')
                    ->with([
                        'periods' => function ($periodsQuery) use ($currentMonthStart) {
                            $periodsQuery
                                ->whereDate('end_date', '>=', $currentMonthStart)
                                ->orderBy('start_date');
                        },
                    ]);
            },
        ]);

        $filters = request()->only(['check_in', 'check_out', 'adults']);
        $hotel->setAttribute('available_rooms', $this->roomService->getAvailableRooms($hotel, $filters));
        $hotel->unsetRelation('rooms');

        return [
            'code' => '',
            'data' => $hotel
        ];
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function checkAvailability(CheckAvailabilityData $dto): array
    {
        $hotel = Hotel::published()->with([
            'rooms.terms.attribute',
            'rooms.terms.translation',
            'rooms.terms.attribute.translation',
        ])->find($dto->hotelId);

        if (!$hotel) {
            throw new NotFoundException(
                errorCode: 'hotel_not_found',
                domain: 'hotel'
            );
        }

        $startDate = Carbon::parse($dto->checkIn)->startOfDay();
        $endDate = Carbon::parse($dto->checkOut)->startOfDay();
        $numberDays = $startDate->diffInDays($endDate);

        if ($numberDays > 30) {
            throw new ValidationException(
                message: __('hotel.errors.max_booking_days'),
                errorCode: 'max_booking_days',
                domain: 'hotel'
            );
        }

        if (!empty($hotel->min_day_stays) && $numberDays < $hotel->min_day_stays) {
            throw new ValidationException(
                message: __('hotel.errors.min_day_stays', ['number' => $hotel->min_day_stays]),
                errorCode: 'min_day_stays',
                domain: 'hotel'
            );
        }

        if (!empty($hotel->min_day_before_booking)) {
            $minDayBefore = Carbon::today()->addDays((int) $hotel->min_day_before_booking);
            if ($startDate->lt($minDayBefore)) {
                throw new ValidationException(
                    message: __('hotel.errors.min_day_before_booking', [
                        'number' => $hotel->min_day_before_booking,
                    ]),
                    errorCode: 'min_day_before_booking',
                    domain: 'hotel'
                );
            }
        }

        return [
            'code' => '',
            'data' => $this->roomService->getAvailableRooms($hotel, $dto->toFilters()),
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

        $hotelsCollection = $this->sortHotelsCollection($hotelsCollection, $dto);

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
        ];

        return [
            'code' => '',
            'data' => $hotels['rows']
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Hotel>  $hotels
     * @return \Illuminate\Support\Collection<int, Hotel>
     */
    protected function sortHotelsCollection($hotels, HotelSearchData $dto)
    {
        $sort = $dto->sort;

        if (empty($sort) && !empty($dto->order_by)) {
            $direction = strtolower((string) ($dto->order_direction ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $column = $dto->order_by;

            $sort = match (true) {
                $column === 'price' && $direction === 'asc' => 'price_asc',
                $column === 'price' && $direction === 'desc' => 'price_desc',
                in_array($column, ['star_rate', 'rating', 'review_score'], true) => 'rating',
                default => 'recommended',
            };
        }

        $sort = $sort ?: 'recommended';

        $items = $hotels->values()->all();

        usort($items, static function ($left, $right) use ($sort) {
            return match ($sort) {
                'price_asc' => ((float) $left->price <=> (float) $right->price)
                    ?: ((int) $left->id <=> (int) $right->id),
                'price_desc' => ((float) $right->price <=> (float) $left->price)
                    ?: ((int) $right->id <=> (int) $left->id),
                'rating' => ((float) $right->star_rate <=> (float) $left->star_rate)
                    ?: ((int) $right->id <=> (int) $left->id),
                default => ((int) $right->is_featured <=> (int) $left->is_featured)
                    ?: ((int) $right->id <=> (int) $left->id),
            };
        });

        return collect($items);
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
