<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Location\Http\Resources\LocationResource;

class HotelSearchResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title'=> $this->resource->title,
            'slug'=> $this->resource->slug,
            'image_url' => $this->resource->getImageUrl()?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'review_score' => $this->resource->review_score,
            'star_rate' => $this->resource->star_rate,
            'has_wish_list' => $this->hasWishList !== null,
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
