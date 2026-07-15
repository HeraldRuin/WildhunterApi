<?php

namespace Modules\Hotel\Filters;

class TermFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->termIds)) {
            $query->join('bc_hotel_term as ht', function ($join) {
                $join->on('ht.target_id', '=', 'bc_hotels.id');
            })->whereIn('ht.term_id', $dto->termIds)
                ->distinct();
        }

        return $next($payload);
    }
}
