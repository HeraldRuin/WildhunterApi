<?php

namespace Modules\Hotel\Filters;

class AnimalFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        // animal_id — одно животное (форма поиска), animalIds — чекбоксы сайдбара (OR).
        // Если переданы оба — пересечение (AND): база должна иметь animal_id и хотя бы одно из animal_ids.
        if (!empty($dto->animal_id)) {
            $query->whereHas('animals', function ($q) use ($dto) {
                $q->where('bc_animals.id', $dto->animal_id)
                    ->where('bc_hotel_animals.status', 'available');
            });
        }

        if (!empty($dto->animalIds)) {
            $query->whereHas('animals', function ($q) use ($dto) {
                $q->whereIn('bc_animals.id', $dto->animalIds)
                    ->where('bc_hotel_animals.status', 'available');
            });
        }

        return $next($payload);
    }
}
