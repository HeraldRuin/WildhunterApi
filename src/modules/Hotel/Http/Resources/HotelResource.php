<?php

namespace Modules\Hotel\Http\Resources;

use Modules\Hotel\Models\Hotel;
use App\Http\Resources\BaseJsonResource;
use Modules\Location\Http\Resources\LocationResource;

class HotelResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Hotel $hotel */
        $hotel = $this->resource;

        return [
            'id' => $hotel->id,
            'title'=> $hotel->title,
            'slug'=> $hotel->slug,
            'address'=> $hotel->address,
            'image_url' => $hotel->getImageUrl()?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'gallery' => $hotel->getGallery(),
            'review_count' => $hotel->reviews->count(),
            'star_rate' => $hotel->star_rate,
            'rooms' => HotelRoomResource::collection($hotel->available_rooms ?? []),
            'location' => LocationResource::make($this->resource->location),
        ];
    }
}
