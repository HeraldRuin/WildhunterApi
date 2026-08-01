<?php

namespace Modules\Location\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class BestLocationResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name'=> $this->resource->name,
            'slug'=> $this->resource->slug,
            'image_url' => $this->resource->getImageUrl('medium'),
            'hotel_count' => $this->resource->hotels_count,
        ];
    }
}
