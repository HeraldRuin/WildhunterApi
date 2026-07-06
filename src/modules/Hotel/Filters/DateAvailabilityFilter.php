<?php

namespace Modules\Hotel\Filters;

use Closure;
use Carbon\Carbon;

class DateAvailabilityFilter
{
    public function handle(array $payload, Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->startDate) && !empty($dto->endDate)) {

            $rangeStart = Carbon::parse($dto->startDate)->format('Y-m-d');

            $rangeEnd = Carbon::parse($dto->endDate)
                ->subDay()
                ->format('Y-m-d');

            $query->excludeBlockedForDates($rangeStart, $rangeEnd);
        }

        return $next($payload);
    }
}
