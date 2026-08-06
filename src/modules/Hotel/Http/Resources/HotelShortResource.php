<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class HotelShortResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'image_url' => $this->resource->getImageUrl() ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
        ];
    }
}
