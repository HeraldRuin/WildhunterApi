<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class OrganisationAnimalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'periods' => AnimalPricePeriodResource::collection($this->resource->periods ?? []),
        ];
    }
}
