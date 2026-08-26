<?php

namespace Modules\Animals\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Animals\Models\Animal;
use Modules\Hotel\Models\Hotel;

class ManageAnimalService
{
    /**
     * @throws ForbiddenException
     */
    public function getManage(User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $hotelId = $hotel->id;

        $animals = Animal::query()
            ->join('bc_hotel_animals as bha', function ($join) use ($hotelId) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $hotelId);
            })
            ->select([
                'bc_animals.*',
                'bha.status as animal_status',
                'bha.hunters_count as hunters_count',
            ])
            ->orderByDesc('bc_animals.id')
            ->get();

        $available = Animal::query()
            ->whereDoesntHave('hotels', function ($q) use ($hotelId) {
                $q->where('bc_hotel_animals.hotel_id', $hotelId);
            })
            ->orderBy('title')
            ->get(['id', 'title']);

        return [
            'animals' => $animals,
            'available' => $available,
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function attach(int $animalId, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $animal = Animal::query()->find($animalId);

        if (!$animal) {
            throw new NotFoundException(
                errorCode: 'animal_not_found',
                domain: 'animal',
            );
        }

        $pivot = $animal->hotels()
            ->where('bc_hotels.id', $hotel->id)
            ->first();

        if ($pivot) {
            return [
                'code' => 'animal_attached',
                'data' => [
                    'id' => $animal->id,
                    'title' => $animal->title,
                    'hunters_count' => (int) ($pivot->pivot->hunters_count ?? 1),
                ],
            ];
        }

        $animal->hotels()->syncWithoutDetaching([
            $hotel->id => ['hunters_count' => 1],
        ]);

        return [
            'code' => 'animal_attached',
            'data' => [
                'id' => $animal->id,
                'title' => $animal->title,
                'hunters_count' => 1,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function updateHuntersCount(int $animalId, int $huntersCount, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertAnimalBelongsToHotel($animalId, $hotel);

        $animal = Animal::query()->find($animalId);

        $animal->hotels()->updateExistingPivot($hotel->id, [
            'hunters_count' => $huntersCount,
        ]);

        return [
            'code' => 'hunters_count_updated',
            'data' => [
                'id' => $animal->id,
                'title' => $animal->title,
                'hunters_count' => $huntersCount,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function detach(int $animalId, User $user): array
    {
        $hotel = $this->resolveHotel($user);
        $this->assertAnimalBelongsToHotel($animalId, $hotel);

        $animal = Animal::query()->find($animalId);
        $animal->hotels()->detach($hotel->id);

        return [
            'code' => 'animal_detached',
            'data' => [
                'id' => $animalId,
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
                errorCode: 'manage_access_denied',
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
}
