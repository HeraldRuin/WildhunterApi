<?php

namespace Modules\Animals\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Animals\Dto\UpdateEntityData;
use Modules\Animals\Models\Animal;
use Modules\Hotel\Models\Hotel;

class TrophyCostService
{
    /**
     * @throws ForbiddenException
     */
    public function getTrophyCost(User $user): Collection
    {
        $hotel = $this->resolveHotel($user);
        $hotelId = $hotel->id;

        return Animal::query()
            ->forHotel($hotelId)
            ->with([
                'trophies' => function ($q) use ($hotelId) {
                    $q->select('id', 'animal_id', 'type')
                        ->with(['hotelPrices' => fn ($q2) => $q2->where('hotel_id', $hotelId)]);
                },
                'fines' => function ($q) use ($hotelId) {
                    $q->select('id', 'animal_id', 'type')
                        ->with(['hotelPrices' => fn ($q2) => $q2->where('hotel_id', $hotelId)]);
                },
                'preparations' => function ($q) use ($hotelId) {
                    $q->select('id', 'animal_id', 'type')
                        ->with(['hotelPrices' => fn ($q2) => $q2->where('hotel_id', $hotelId)]);
                },
            ])
            ->orderByDesc('bc_animals.id')
            ->get();
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function update(UpdateEntityData $data, Model $entity, string $type, User $user): array
    {
        $hotel = $this->resolveHotel($user);

        $service = $entity->forHotel($hotel->id)->where('id', $entity->id)->first();

        if (!$service) {
            throw new NotFoundException(
                errorCode: $type . '_not_found',
                domain: 'animal',
            );
        }

        $service->setHotelPrice($hotel->id, $data->price);

        return [
            'code' => $type . '_saved',
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function resolveHotel(User $user): Hotel
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'trophy_cost_access_denied',
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
}
