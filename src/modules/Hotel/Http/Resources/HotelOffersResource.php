<?php

namespace Modules\Hotel\Http\Resources;

use Modules\Hotel\Models\Hotel;
use App\Http\Resources\BaseJsonResource;
use Modules\Location\Http\Resources\LocationResource;

class HotelOffersResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Hotel $hotel */
        $hotel = $this->resource;

        return [
            'id' => $hotel->id,
            'title'=> $hotel->title,
            'slug'=> $hotel->slug,
            'map_lat'=> $this->resource->map_lat,
            'map_lng'=> $this->resource->map_lng,
            'image_url' => $hotel->getImageUrl('medium') ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'star_rate' => $hotel->star_rate,
            'has_food' => (bool) $hotel->has_food,
            'price' => $hotel->price,
            'review_count' => $hotel->reviews->count(),
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
