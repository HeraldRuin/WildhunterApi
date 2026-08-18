<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Attributes\Http\Resources\AttributesResource;
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
            'attributes' => $this->whenLoaded('terms', function () use ($room) {
                return AttributesResource::collection($this->attributesByGroup($room));
            }),
        ];
    }

    private function attributesByGroup(HotelRoom $room)
    {
        return $room->terms
            ->groupBy('attr_id')
            ->filter(fn ($terms) => $terms->first()?->attribute)
            ->sortBy(fn ($terms) => $terms->first()->attribute->position ?? 0)
            ->map(function ($terms) {
                $attribute = clone $terms->first()->attribute;
                $attribute->setRelation('terms', $terms->values());

                return $attribute;
            })
            ->values();
    }
}



