<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class RoomCalendarListResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'number' => (int) ($this->resource->number ?? 0),
            'beds' => $this->resource->beds !== null ? (int) $this->resource->beds : null,
            'adults' => $this->resource->adults !== null ? (int) $this->resource->adults : null,
            'price' => (float) ($this->resource->price ?? 0),
            'status' => $this->resource->status,
            'image_url' => $this->resource->getImageUrl() ?: '',
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
