<?php

namespace Modules\Hotel\Services;

use Modules\Hotel\Models\Hotel;
use Illuminate\Pipeline\Pipeline;
use Modules\Hotel\Dto\HotelSearchData;

class HotelSearchService
{
    public function search(HotelSearchData $dto)
    {
        $userId = auth()->id();

        $query = Hotel::query()
            ->select('bc_hotels.*')
            ->where('bc_hotels.status', 'publish');

        $payload = app(Pipeline::class)
            ->send([
                'query' => $query,
                'dto' => $dto,
            ])
            ->through($this->filters())
            ->thenReturn();

        return $payload['query']
            ->with([
                'location',
                'hasWishList' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                },
                'reviews',
                'translation',
            ]);
    }

    private function filters(): array
    {
        return [
            \Modules\Hotel\Filters\LocationFilter::class,
            \Modules\Hotel\Filters\PriceFilter::class,
//            \Modules\Hotel\Filters\AnimalFilter::class,
            \Modules\Hotel\Filters\StarRateFilter::class,
            \Modules\Hotel\Filters\DateAvailabilityFilter::class,
        ];
    }
}
