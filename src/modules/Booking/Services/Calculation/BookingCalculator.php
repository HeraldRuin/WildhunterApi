<?php

namespace Modules\Booking\Services\Calculation;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Models\BookingService;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingCalculator
{
    public function getServiceCount(Booking $booking, $services, array $allowedTypes): array
    {
        $grouped = $services->groupBy('service_type');

        $totals = collect();

        foreach ($allowedTypes as $type) {

            $items = $grouped->get($type, collect());

            $sum = $items->sum(function ($item) use ($type) {

                if ($type === AddetionalPrice::FOOD) {
                    return $item->foodLineAmount();
                }

                return $item->price;
            });

            $totals->put($type, round($sum));
        }

        return [
            'trophy' => $totals->get(AddetionalPrice::TROPHY, 0),
            'penalty' => $totals->get(AddetionalPrice::PENALTY, 0),
            'food' => $totals->get(AddetionalPrice::FOOD, 0),
            'preparation' => $totals->get(AddetionalPrice::PREPARATION, 0),
            'additional' => $totals->get(AddetionalPrice::ADDETIONAL, 0),

            'total' => $totals->sum(),
        ];
    }
    public function getServiceMyCount(Booking $booking, $user, $huntersCount, $services, array $allowedTypes): array
    {
        $grouped = $services->groupBy('service_type');

        $totals = collect();

        foreach ($allowedTypes as $type) {

            $items = $grouped->get($type, collect());

            $sum = $items->sum(function ($item) use ($type, $user, $huntersCount) {

                if ($type === AddetionalPrice::FOOD) {
                    $line = $item->foodLineAmount();

                    if ($item->calculation_type === AddetionalPrice::INDIVIDUAL) {
                        return (int) $item->hunter_id === (int) $user->id ? $line : 0;
                    }

                    return $huntersCount > 0 ? $line / $huntersCount : 0;
                }
                if ($type === AddetionalPrice::ADDETIONAL) {

                    if ($item->service_type === AddetionalPrice::ADDETIONAL && $item->calculation_type === AddetionalPrice::PERSON) {
                        return $item->price / $huntersCount;
                    }

                    if ($item->service_type === AddetionalPrice::ADDETIONAL && $item->calculation_type === AddetionalPrice::INDIVIDUAL) {
                        if ($item->hunter_id !== $user->id) {
                            return 0;
                        }
                    }

                    return $item->price;
                }
                if ($type === AddetionalPrice::PENALTY) {
                    if ($item->hunter_id !== $user->id) {
                        return 0;
                    }
                    return $item->price;
                }

                return $item->price / $huntersCount;
            });

            $totals->put($type, round($sum));
        }

        return [
            'trophy' => $totals->get(AddetionalPrice::TROPHY, 0),
            'penalty' => $totals->get(AddetionalPrice::PENALTY, 0),
            'food' => $totals->get(AddetionalPrice::FOOD, 0),
            'preparation' => $totals->get(AddetionalPrice::PREPARATION, 0),
            'additional' => $totals->get(AddetionalPrice::ADDETIONAL, 0),

            'total' => $totals->sum(),
        ];
    }

    public function calculateRooms($booking, User $user): array
    {
        $places = BookingRoomPlace::where('booking_id', $booking->id)->get();
        $rooms = $places->groupBy('room_id');

        $roomPrices = HotelRoomBooking::where('booking_id', $booking->id)
            ->whereIn('room_id', $rooms->keys())
            ->pluck('price', 'room_id');

        $result = [];

        foreach ($rooms as $roomId => $roomPlaces) {
            $totalPlaces = $roomPlaces->count();
            $myPlaces = $roomPlaces->where('user_id', $user->id)->count();
            $roomPriceAllDay = $roomPrices[$roomId] ?? 0;
            $pricePerPlace = $totalPlaces > 0 ? $roomPriceAllDay / $totalPlaces : 0;
            $myCost = $pricePerPlace * $myPlaces;

            $result[] = [
                'room_id' => $roomId,
                'total_places' => $totalPlaces,
                'my_places' => $myPlaces,
                'price_per_place' => round($pricePerPlace),
                'my_cost' => round($myCost),
            ];
        }
        return $result;
    }

    public function getAccommodationCost(Booking $booking): float
    {
        return round($booking->total);
    }
    public function getMyAccommodationCost(Booking $booking, User $user): float
    {
        $rooms = $this->calculateRooms($booking, $user);

        return array_sum(array_column($rooms, 'my_cost'));
    }

    public function calculateTrophies(Collection $trophies, int $huntersCount): array
    {
        $result = [];

        foreach ($trophies as $trophy) {
            $animalName = $trophy->animal?->title ?? '';

            $count = $this->lineCount($trophy);

            $result[] = [
                'name' => $animalName . ' (' . $trophy->type . ')',
                'count' => $count,
                'my_count' => $count,
                'total_cost' => round((float)$trophy->price),
                'my_cost' => $huntersCount > 0 ? round($trophy->price / $huntersCount): 0,
                'my_service_cost' => 0,
            ];
        }

        return $result;
    }

    public function calculateMeals(Collection $meals, int $huntersCount, Booking $booking, User $user): array
    {
        $result = [];

        $grouped = $meals->groupBy(
            fn ($food) => ($food->type ?? '') . '|' . ($food->calculation_type ?? '') . '|' . ($this->servicePlace($food) ?? ''),
        );

        foreach ($grouped as $items) {
            $first = $items->first();
            $totalCost = round($items->sum(fn ($food) => $food->foodLineAmount()));

            if ($first->calculation_type === AddetionalPrice::INDIVIDUAL) {
                $myCost = round($items
                    ->filter(fn ($food) => (int) $food->hunter_id === (int) $user->id)
                    ->sum(fn ($food) => $food->foodLineAmount()));
                $myServiceCost = $myCost;
            } else {
                $myCost = $huntersCount > 0 ? round($totalCost / $huntersCount) : 0;
                $myServiceCost = 0;
            }

            $result[] = [
                'name' => $first->type,
                'place' => $this->servicePlace($first),
                'count' => $this->lineCount($items),
                'my_count' => $this->myLineCount($items, $user),
                'total_cost' => $totalCost,
                'my_cost' => $myCost,
                'my_service_cost' => $myServiceCost,
            ];
        }

        return $result;
    }

    public function calculatePenalties(Collection $penalties, User $user): array
    {
        $result = [];

        $groupedByAnimalType = $penalties->groupBy(fn($item) => $item->animal_id . '|' . $item->type);

        foreach ($groupedByAnimalType as $items) {
            $first = $items->first();
            $animalName = $first->animal?->title ?? '';
            $totalCost = $items->sum('price');

            $myCost = $items
                ->where('hunter_id', $user->id)
                ->sum('price');

            $myServiceCost = round($myCost);

            $result[] = [
                'name' => $animalName . ' (' . $first->type . ')',
                'count' => $this->lineCount($items),
                'my_count' => $this->myLineCount($items, $user),
                'total_cost' => round($totalCost),
                'my_cost' => $myServiceCost,
                'my_service_cost' => $myServiceCost,
            ];
        }

        return $result;
    }

    public function calculatePreparations(Collection $preparations, int $huntersCount): array
    {
        $result = [];

        foreach ($preparations as $preparation) {
            $animalName = $preparation->animal?->title ?? '';
            $totalCost = round($preparation->price);

            $count = $this->lineCount($preparation);

            $result[] = [
                'name' => $animalName . ' (' . $preparation->type . ')',
                'count' => $count,
                'my_count' => $count,
                'total_cost' => $totalCost,
                'my_cost' => $huntersCount > 0 ? round($preparation->price / $huntersCount): 0,
                'my_service_cost' => 0,
            ];
        }

        return $result;
    }

    public function calculateAdditional(Collection $additional, User $user, $huntersCount): array
    {
        $result = [];

        $grouped = $additional->groupBy(
            fn ($item) => ($item->type ?? '') . '|' . $this->servicePlace($item),
        );

        foreach ($grouped as $items) {
            $first = $items->first();
            $totalCost = (int) round($items->sum(fn ($item) => (float) $item->price));
            $myCost = (int) round($items->sum(
                fn ($item) => (float) ($this->calculateAddetionalByType($item, $user, $huntersCount) ?? 0),
            ));
            $myServiceCost = (int) round($items->sum(function ($item) use ($user) {
                if (($item->calculation_type ?? null) !== AddetionalPrice::INDIVIDUAL) {
                    return 0;
                }

                if ((int) $item->hunter_id !== (int) $user->id) {
                    return 0;
                }

                return (float) $item->price;
            }));

            $result[] = [
                'name' => $first->type,
                'place' => $this->servicePlace($first),
                'count' => $this->lineCount($items),
                'my_count' => $this->myLineCount($items, $user),
                'total_cost' => $totalCost,
                'my_cost' => $myCost,
                'my_service_cost' => $myServiceCost,
            ];
        }

        return $result;
    }

    private function lineCount(Collection|BookingService $items): int
    {
        if ($items instanceof Collection) {
            return (int) $items->sum(fn ($item) => (int) ($item->count ?? 0));
        }

        return (int) ($items->count ?? 0);
    }

    private function myLineCount(Collection $items, User $user): int
    {
        return (int) $items->sum(function ($item) use ($user) {
            if ($this->countsOnlyForOwner($item) && (int) $item->hunter_id !== (int) $user->id) {
                return 0;
            }

            return (int) ($item->count ?? 0);
        });
    }

    private function countsOnlyForOwner($item): bool
    {
        if (($item->service_type ?? null) === AddetionalPrice::PENALTY) {
            return true;
        }

        return ($item->calculation_type ?? null) === AddetionalPrice::INDIVIDUAL;
    }

    private function servicePlace($item): string
    {
        $place = $item->place ?? null;

        if ($place === null || $place === '') {
            return BookingService::PLACE_BASE;
        }

        return (string) $place;
    }

    public function calculateAddetionalByType($item, User $user, $huntersCount)
    {
        if ($item->service_type === AddetionalPrice::ADDETIONAL && $item->calculation_type === AddetionalPrice::PERSON) {
            return round($item->price / $huntersCount);
        }

        if ($item->service_type === AddetionalPrice::ADDETIONAL && $item->calculation_type === AddetionalPrice::INDIVIDUAL) {
            if ($item->hunter_id !== $user->id) {
                return 0;
            }
            return round($item->price);
        }
    }

    public function calculateSpendings(Collection $spendings, User $user, int $huntersCount): array
    {
        $result = [];
        $totalMyDebt = 0;
        $totalDueToMe = 0;
        $totalSpending = 0;
        $hunters = max(1, $huntersCount);

        foreach ($spendings as $spending) {
            $hunter = $spending->hunter;
            $isMine = $spending->hunter_id === $user->id;
            $share = round($spending->price / $hunters);
            $myCost = $isMine ? 0 : $share;
            $dueToMe = $isMine ? round($spending->price) - $share : null;

            $totalMyDebt += $myCost;
            $totalDueToMe += $dueToMe ?? 0;
            $totalSpending += $spending->price;

            $name = (($hunter->last_name ?? '—') . ' (' . ($spending->comment ?? '') . ')');

            $result[] = [
                'name' => $name,
                'total_cost' => round($spending->price),
                'my_cost' => $myCost,
                'due_to_me' => $dueToMe,
            ];
        }

        return [
            'items' => $result,
            'total_spending' => round($totalSpending),
            'total_my_debt' => $totalMyDebt,
            'total_due_to_me' => $totalDueToMe,
        ];
    }

    public function getSpendings(Collection $spendings, User $user, int $huntersCount): array
    {
        $data = $this->calculateSpendings($spendings, $user, $huntersCount);

        return [
            'title_name' => 'Итог охотникам',
            'items' => $data['items'],
            'total_cost' => $data['total_spending'],
            'my_cost' => $data['total_my_debt'],
            'due_to_me' => $data['total_due_to_me'],
        ];
    }

    public function calculateBaseTotal(Booking $booking, $services, int $huntersCount): float
    {
        $result = $this->getServiceCount($booking, $services, [
            AddetionalPrice::FOOD,
            AddetionalPrice::PREPARATION,
            AddetionalPrice::ADDETIONAL,
        ]);

        return $result['total'];
    }

    public function calculateHuntingExpenses(Booking $booking, $services, int $huntersCount): float
    {
        $result = $this->getServiceCount($booking, $services, [
            AddetionalPrice::TROPHY,
            AddetionalPrice::PENALTY,
        ]);

        return $this->calculateOrganisationHunting($booking, $huntersCount) + $result['total'];
    }

    public function calculateStayEventTotal(Booking $booking, $services, int $huntersCount): float
    {
        return round($this->getAccommodationCost($booking) + $this->calculateBaseTotal($booking, $services, $huntersCount));
    }

    public function calculateMyBaseTotal(Booking $booking, User $user, $services, int $huntersCount): float
    {
        $result = $this->getServiceMyCount($booking, $user, $huntersCount, $services, [
            AddetionalPrice::FOOD,
            AddetionalPrice::PREPARATION,
            AddetionalPrice::ADDETIONAL,
        ]);

        return round(
            $this->getMyAccommodationCost($booking, $user)
            + $result['total']
            - $this->myPrepaymentMade($booking->prepaymentAmount(), $huntersCount)
        );
    }
    public function calculateMyBaseTotalOnlyHunting(Booking $booking, User $user, $services, int $huntersCount): float
    {
        $result = $this->getServiceMyCount($booking, $user, $huntersCount, $services, [
            AddetionalPrice::FOOD,
            AddetionalPrice::PREPARATION,
            AddetionalPrice::ADDETIONAL,
        ]);

        return $result['total'];
    }
    public function getAccommodation(Booking $booking, User $user): array
    {
        return [
            'title_name' => 'Проживание, ' . plural_sutki($booking->duration_days),
            'total_cost' => $this->getAccommodationCost($booking),
            'my_cost' => $this->getMyAccommodationCost($booking, $user),
        ];
    }
    public function calculateOrganisationHunting(Booking $booking, int $huntersCount): float
    {
        if (!$booking->total_hunting || $huntersCount <= 0) {
            return 0;
        }

        // Сумма охоты фиксируется при создании брони и не уменьшается,
        // если на сбор пришло меньше охотников, чем было забронировано.
        return round((float) $booking->amount_hunting);
    }
    public function calculateMyOrganisationHunting(Booking $booking, int $huntersCount): float
    {
        $total = $this->calculateOrganisationHunting($booking, $huntersCount);

        if ($huntersCount <= 0) {
            return 0;
        }

        return round($total / $huntersCount);
    }

    public function getOrganisationHunting(Booking $booking, int $huntersCount): array
    {
        return [
            'title_name' => 'Организация охоты',
            'total_cost' => $this->calculateOrganisationHunting($booking, $huntersCount),
            'my_cost' => $this->calculateMyOrganisationHunting($booking, $huntersCount),
        ];
    }
    public function getPrepaymentMade(Booking $booking, int $huntersCount): array
    {
        return [
            'title_name' => 'Внесена предоплата',
            'total_cost' => $this->basePrepaymentMade($booking),
            'my_cost' => $this->myPrepaymentMade($booking->prepaymentAmount(), $huntersCount),
        ];
    }
    public function basePrepaymentMade(Booking $booking): float
    {
        return $booking->prepaymentAmount();
    }
    public function myPrepaymentMade($bookingTotal, int $huntersCount): float
    {
        return round($bookingTotal / $huntersCount);
    }
    public function getBalanceBase(Booking $booking, User $user, $services, int $huntersCount): array
    {
        $baseServices = $this->servicesAtBase($services);

        return [
            'title_name' =>  'Остаток базе',
            'total_cost' => max(0, round($this->calculateStayEventTotal($booking, $baseServices, $huntersCount) - $this->basePrepaymentMade($booking))),
            'my_cost' => $this->calculateMyBaseTotal($booking, $user, $baseServices, $huntersCount),
        ];
    }
    public function getBalanceBaseHunting(Booking $booking, User $user, $services, int $huntersCount): array
    {
        $baseServices = $this->servicesAtBase($services);

        return [
            'title_name' =>  'Остаток базе',
            'total_cost' => $this->calculateBaseTotal($booking, $baseServices, $huntersCount),
            'my_cost' => $this->calculateMyBaseTotalOnlyHunting($booking, $user, $baseServices, $huntersCount),
        ];
    }

    private function servicesAtBase($services): Collection
    {
        return collect($services)
            ->filter(fn ($item) => $this->servicePlace($item) === BookingService::PLACE_BASE)
            ->values();
    }

    //Подсчет в историю бронирования в колонку оплата (админа базы)
    public function getBookingTotal(Booking $booking, $services, int $huntersCount): array
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return [
                'prepaid_total' => 0,
                'base_total' => $this->calculateBaseTotal($booking, $services, $huntersCount),
                'total' => 0,
            ];
        }

        if ($booking->type === Booking::BookingTypeHotel || $booking->type === Booking::BookingTypeHotelAnimal) {
            $prepaidTotal = $this->basePrepaymentMade($booking);
            $baseExpenses = $this->calculateStayEventTotal($booking, $services, $huntersCount);
            $huntingExpenses = $this->calculateHuntingExpenses($booking, $services, $huntersCount);

            return [
                'prepaid_total' => $prepaidTotal,
                'base_total' => max(0, round($baseExpenses - $prepaidTotal)),
                'total' => max(0, round($baseExpenses + $huntingExpenses - $prepaidTotal)),
            ];
        }

        return [
            'prepaid_total' => 0,
            'base_total' => 0,
            'total' => 0,
        ];
    }
}
