<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class TrophyCostItemResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        $hotelPrice = $this->resource->hotelPrices->first();

        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'price' => $hotelPrice?->price !== null
                ? (float) $hotelPrice->price
                : null,
        ];
    }
}
