<?php

namespace Modules\Hotel\Filters;

class AnimalFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->animal_id)) {
            $query->whereHas('animals', function ($q) use ($dto) {
                $q->where('bc_animals.id', $dto->animal_id)
                    ->where('bc_hotel_animals.status', 'available');
            });
        }

        return $next($payload);
    }
}
