<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Hotel\Models\HotelRoom;

class HotelRoomResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var HotelRoom $room */
        $room = $this->resource;

        return [
            'id' => $room->id,
            'title' => $room->title,
            'price' => $room->calculated_price ?? $room->price,
            'nights' => $room->calculated_nights ?? 1,
            'size' => $room->size,
            'beds' => $room->beds,
            'adults' => $room->adults,
            'children' => $room->children,
            'number_selected' => $room->number_selected ?? 0,
            'number' => $room->available_number ?? $room->number,
            'image_url' => $room->getImageUrl() ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'gallery' => $room->getGallery(),
        ];
    }
}



