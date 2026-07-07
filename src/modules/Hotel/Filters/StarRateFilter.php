<?php

namespace Modules\Hotel\Filters;

use Modules\Review\Models\Review;

class StarRateFilter
{
    public function handle($payload, \Closure $next)
    {
        $query = $payload['query'];
        $dto = $payload['dto'];

        if (!empty($dto->star_rate)) {
            $scores = Review::getScoresByRatings($dto->star_rate);

            if (!empty($scores)) {
                $query->whereIn('star_rate', $scores);
            }
        }

        return $next($payload);
    }
}
