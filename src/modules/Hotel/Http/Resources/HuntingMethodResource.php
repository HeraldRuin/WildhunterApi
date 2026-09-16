<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class HuntingMethodResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
        ];
    }
}
