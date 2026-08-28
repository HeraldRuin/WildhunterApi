<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Location\Http\Resources\LocationResource;

class HotelManageListResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'image_url' => $this->resource->getImageUrl() ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'price' => $this->resource->price,
            'status' => $this->resource->status,
            'status_label' => __('hotel.statuses.' . $this->resource->status),
            'updated_at' => $this->resource->updated_at,
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
