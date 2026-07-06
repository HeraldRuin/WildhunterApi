<?php

namespace Modules\Hotel\Filters;

use Modules\Location\Models\Location;

class LocationFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->location_id)) {

            $location = Location::query()
                ->where('id', $dto->location_id)
                ->where('status', 'publish')
                ->first();

            if ($location) {
                $query->whereHas('location', function ($q) use ($location) {
                    $q->where('_lft', '>=', $location->_lft)
                        ->where('_rgt', '<=', $location->_rgt);
                });
            }
        }

        if (!empty($dto->locationIds)) {

            $query->whereIn('location_id', $dto->locationIds);
        }

        return $next($payload);
    }
}
