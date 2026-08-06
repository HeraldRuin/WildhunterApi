<?php

namespace Modules\Animals\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Animals\Dto\CheckAnimalAvailabilityData;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalDate;
use Modules\Animals\Models\AnimalPricePeriod;
use Modules\Hotel\Models\Hotel;

class AnimalService
{
    public function getAnimals(): array
    {
        $animals = Animal::where('status', 'publish')
            ->get();

        return [
            'animals' => $animals,
        ];
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function checkAvailability(CheckAnimalAvailabilityData $dto): array
    {
        $hotel = Hotel::published()->find($dto->hotelId);

        if (!$hotel) {
            throw new NotFoundException(
                errorCode: 'hotel_not_found',
                domain: 'animal'
            );
        }

        $animal = Animal::query()
            ->where('status', 'publish')
            ->find($dto->animalId);

        if (!$animal) {
            throw new NotFoundException(
                errorCode: 'animal_not_found',
                domain: 'animal'
            );
        }

        $hotelAnimal = DB::table('bc_hotel_animals')
            ->where('hotel_id', $dto->hotelId)
            ->where('animal_id', $dto->animalId)
            ->where('status', 'available')
            ->first();

        if (!$hotelAnimal) {
            throw new ValidationException(
                message: __('animal.errors.animal_not_available_at_hotel'),
                errorCode: 'animal_not_available_at_hotel',
                domain: 'animal'
            );
        }

        if ($dto->checkIn && $dto->checkOut) {
            $stayStart = Carbon::parse($dto->checkIn)->startOfDay();
            $stayEnd = Carbon::parse($dto->checkOut)->startOfDay();
            $huntDate = Carbon::parse($dto->hunterData)->startOfDay();

            if ($huntDate->lte($stayStart) || $huntDate->gt($stayEnd)) {
                throw new ValidationException(
                    message: __('animal.errors.hunt_date_out_of_stay'),
                    errorCode: 'hunt_date_out_of_stay',
                    domain: 'animal'
                );
            }
        }

        $range = AnimalDate::getDatesInRanges($dto->hunterData, $dto->hunterData, $dto->animalId)->first();
        $excludedDates = $range && $range->excluded_dates
            ? json_decode($range->excluded_dates, true)
            : [];

        if (!$range || in_array($dto->hunterData, $excludedDates ?? [], true)) {
            throw new ValidationException(
                message: __('animal.errors.animal_unavailable_on_date'),
                errorCode: 'animal_unavailable_on_date',
                domain: 'animal'
            );
        }

        $period = AnimalPricePeriod::query()
            ->where('animal_id', $dto->animalId)
            ->whereDate('start_date', '<=', $dto->hunterData)
            ->whereDate('end_date', '>=', $dto->hunterData)
            ->first();

        if (!$period) {
            throw new ValidationException(
                message: __('animal.errors.price_period_not_found'),
                errorCode: 'price_period_not_found',
                domain: 'animal'
            );
        }

        $minHuntersCount = (int) ($hotelAnimal->hunters_count ?? 0);
        if ($minHuntersCount > 0 && $dto->hunters < $minHuntersCount) {
            throw new ValidationException(
                errorCode: 'min_hunters',
                domain: 'animal',
                context: [
                    'min' => $minHuntersCount,
                    'selected' => $dto->hunters,
                ]
            );
        }

        return [
            'available' => true,
            'price' => (float) $period->price,
        ];
    }
}
