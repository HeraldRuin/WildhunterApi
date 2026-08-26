<?php

namespace Modules\Animals\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Animals\Dto\AnimalPricePeriodUpdateData;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalPricePeriod;
use Modules\Hotel\Models\Hotel;

class OrganisationService
{
    /**
     * @throws ForbiddenException
     */
    public function getOrganisation(User $user): Collection
    {
        $hotel = $this->resolveHotel($user);

        return Animal::query()
            ->forHotel($hotel->id)
            ->with(['periods' => fn ($q) => $q->orderBy('start_date')])
            ->orderByDesc('bc_animals.id')
            ->get();
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function createPeriod(int $animalId, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertAnimalBelongsToHotel($animalId, $hotel);

        $period = AnimalPricePeriod::create([
            'animal_id' => $animalId,
            'start_date' => null,
            'end_date' => null,
            'price' => null,
        ]);

        return [
            'code' => 'period_saved',
            'data' => [
                'animal_id' => $animalId,
                'period' => $period,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updatePeriod(
        AnimalPricePeriod $period,
        AnimalPricePeriodUpdateData $data,
        User $user,
    ): array {
        $hotel = $this->resolveHotel($user);
        $this->assertPeriodBelongsToHotel($period, $hotel);

        $period->update([
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'price' => $data->price,
        ]);

        return [
            'code' => 'period_updated',
            'data' => [
                'period' => $period->fresh(),
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function deletePeriod(AnimalPricePeriod $period, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertPeriodBelongsToHotel($period, $hotel);

        $periodId = $period->id;
        $period->delete();

        return [
            'code' => 'period_deleted',
            'data' => [
                'id' => $periodId,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function resolveHotel(User $user): Hotel
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'organisation_access_denied',
                domain: 'animal',
            );
        }

        $hotel = $user->hotels()->first();

        if (!$hotel) {
            throw new ForbiddenException(
                errorCode: 'hotel_required',
                domain: 'animal',
            );
        }

        return $hotel;
    }

    /**
     * @throws NotFoundException
     */
    private function assertAnimalBelongsToHotel(int $animalId, Hotel $hotel): void
    {
        $exists = Animal::query()
            ->forHotel($hotel->id)
            ->where('bc_animals.id', $animalId)
            ->exists();

        if (!$exists) {
            throw new NotFoundException(
                errorCode: 'animal_not_found',
                domain: 'animal',
            );
        }
    }

    /**
     * @throws NotFoundException
     */
    private function assertPeriodBelongsToHotel(AnimalPricePeriod $period, Hotel $hotel): void
    {
        $exists = Animal::query()
            ->forHotel($hotel->id)
            ->where('bc_animals.id', $period->animal_id)
            ->exists();

        if (!$exists) {
            throw new NotFoundException(
                errorCode: 'period_not_found',
                domain: 'animal',
            );
        }
    }
}
