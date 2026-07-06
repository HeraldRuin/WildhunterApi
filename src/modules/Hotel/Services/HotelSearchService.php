<?php

namespace Modules\Hotel\Services;

use Illuminate\Pipeline\Pipeline;
use Modules\Hotel\Dto\HotelSearchData;
use Modules\Hotel\Models\Hotel;

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
//            \App\Services\Hotel\Filters\PriceFilter::class,
//            \App\Services\Hotel\Filters\AnimalFilter::class,
//            \App\Services\Hotel\Filters\StarRateFilter::class,
            \Modules\Hotel\Filters\DateAvailabilityFilter::class,
        ];
    }
}
