<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Hotel\Models\Hotel;
use Modules\Location\Http\Resources\LocationResource;
use Modules\Media\Helpers\FileHelper;

class HotelManageEditResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Hotel $hotel */
        $hotel = $this->resource;

        return [
            'id' => $hotel->id,
            'title' => $hotel->title,
            'slug' => $hotel->slug,
            'content' => $hotel->content,
            'star_rate' => $hotel->star_rate,
            'address' => $hotel->address,
            'image_id' => $hotel->image_id,
            'image_url' => $hotel->getImageUrl() ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'gallery' => $this->galleryWithIds($hotel),
            'policy' => $hotel->policy,
            'surrounding' => $hotel->surrounding,
            'price' => $hotel->price,
            'extra_price' => $hotel->extra_price,
            'service_fee' => $hotel->service_fee,
            'map_lat' => $hotel->map_lat,
            'map_lng' => $hotel->map_lng,
            'location_id' => $hotel->location_id,
            'location' => LocationResource::make($hotel->location),
            'status' => $hotel->status,
            'status_label' => __('hotel.statuses.' . $hotel->status),
            'has_food' => (bool) $hotel->has_food,
            'term_ids' => $hotel->relationLoaded('terms')
                ? $hotel->terms->pluck('id')->values()->all()
                : [],
        ];
    }

    private function galleryWithIds(Hotel $hotel): array
    {
        if (empty($hotel->gallery)) {
            return [];
        }

        $listItem = [];

        foreach (explode(',', (string) $hotel->gallery) as $item) {
            $item = trim($item);

            if ($item === '') {
                continue;
            }

            $large = FileHelper::url($item, 'full');

            if (!empty($large)) {
                $listItem[] = [
                    'id' => (int) $item,
                    'large' => $large,
                    'medium' => FileHelper::url($item, 'medium'),
                    'thumb' => FileHelper::url($item, 'thumb'),
                ];
            }
        }

        return $listItem;
    }
}
