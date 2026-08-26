<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class AnimalPricePeriodResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'start_date' => $this->formatDate($this->resource->start_date),
            'end_date' => $this->formatDate($this->resource->end_date),
            'price' => $this->resource->price !== null
                ? (float) $this->resource->price
                : null,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return $value->format('Y-m-d');
    }
}
