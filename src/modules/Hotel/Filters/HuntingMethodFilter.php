<?php

namespace Modules\Hotel\Filters;

class HuntingMethodFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->huntingMethodIds)) {
            $query->whereHas('huntingMethods', function ($q) use ($dto) {
                $q->whereIn('bc_hunting_methods.id', $dto->huntingMethodIds);
            });
        }

        return $next($payload);
    }
}
