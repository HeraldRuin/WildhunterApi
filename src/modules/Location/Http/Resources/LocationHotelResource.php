<?php

namespace Modules\Location\Http\Resources;

use Modules\Hotel\Models\Hotel;
use App\Http\Resources\BaseJsonResource;

class LocationHotelResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Hotel $hotel */
        $hotel = $this->resource;

        return [
            'id' => $hotel->id,
            'title' => $hotel->title,
            'slug' => $hotel->slug,
            'image_url' => $hotel->getImageUrl('medium') ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'star_rate' => $hotel->star_rate,
            'price' => $hotel->price,
            'review_count' => $hotel->reviews->count(),
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
