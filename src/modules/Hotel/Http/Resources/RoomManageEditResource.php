<?php

namespace Modules\Hotel\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Hotel\Models\HotelRoom;
use Modules\Media\Helpers\FileHelper;

class RoomManageEditResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var HotelRoom $room */
        $room = $this->resource;

        return [
            'id' => $room->id,
            'title' => $room->title,
            'content' => $room->content,
            'image_id' => $room->image_id,
            'image_url' => $room->getImageUrl() ?: asset('uploads/0000/1/2026/11/14/no_image.png'),
            'gallery' => $this->galleryWithIds($room),
            'price' => $room->price,
            'number' => $room->number,
            'beds' => $room->beds,
            'size' => $room->size,
            'adults' => $room->adults,
            'children' => $room->children,
            'status' => $room->status,
            'status_label' => __('hotel.statuses.' . $room->status),
            'min_day_stays' => $room->min_day_stays,
            'ical_import_url' => $room->ical_import_url,
            'video' => $room->video,
            'term_ids' => $room->relationLoaded('terms')
                ? $room->terms->pluck('id')->values()->all()
                : [],
        ];
    }

    private function galleryWithIds(HotelRoom $room): array
    {
        if (empty($room->gallery)) {
            return [];
        }

        $listItem = [];

        foreach (explode(',', (string) $room->gallery) as $item) {
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
