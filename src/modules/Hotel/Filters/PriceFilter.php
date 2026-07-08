<?php

namespace Modules\Hotel\Filters;

class PriceFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];
        \Log::info('Result', [
            'Result' => $dto,
        ]);
        if (!empty($dto->price)) {
            $min = $dto->price['min'] ?? null;
            $max = $dto->price['max'] ?? null;

            if ($min !== null && $max !== null) {
                $query->whereBetween('bc_hotels.price', [$min, $max]);
            } elseif ($min !== null) {
                $query->where('bc_hotels.price', '>=', $min);
            } elseif ($max !== null) {
                $query->where('bc_hotels.price', '<=', $max);
            }
        }

        return $next($payload);
    }
}
