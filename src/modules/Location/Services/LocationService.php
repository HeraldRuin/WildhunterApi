<?php

namespace Modules\Location\Services;

use App\Exceptions\NotFoundException;
use Modules\Location\Models\Location;
use Modules\Location\Dto\LocationFilterData;

class LocationService
{
    public function getBestLocations(LocationFilterData $dto): array
    {
        $locations = Location::published()
            ->withCount('hotels')
            ->when($dto->order_by, function ($q) use ($dto) {
                $q->orderBy($dto->order_by, $dto->order_direction ?? 'asc');
            })
            ->when($dto->limit, fn($q) => $q->limit($dto->limit))
            ->get();

        return [
            'locations' => $locations
        ];
    }
    public function getLocations(LocationFilterData $dto): array
    {
        $locations = Location::published()->get();

        return [
            'locations' => $locations
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function getLocationHotels(int $id): array
    {
        $location = Location::published()->find($id);

        if (!$location) {
            throw new NotFoundException(
                errorCode: 'location_not_found',
                domain: 'location'
            );
        }

        $hotels = $location->hotels()
            ->published()
            ->withCount('reviews')
            ->get();

        return [
            'hotels' => $hotels
        ];
    }
}
