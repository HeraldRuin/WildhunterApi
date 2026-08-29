<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class TrophyCostAnimalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'trophies' => TrophyCostItemResource::collection($this->resource->trophies ?? []),
            'fines' => TrophyCostItemResource::collection($this->resource->fines ?? []),
            'preparations' => TrophyCostItemResource::collection($this->resource->preparations ?? []),
        ];
    }
}
