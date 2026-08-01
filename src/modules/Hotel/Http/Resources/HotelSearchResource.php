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
            'map_lat'=> $this->resource->map_lat,
            'map_lng'=> $this->resource->map_lng,
            'image_url' => $this->resource->getImageUrl()?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'price' => $this->resource->price,
            'review_count' => $this->resource->reviews->count(),
            'star_rate' => $this->resource->star_rate,
            'is_in_wishList' => $this->hasWishList !== null,
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
