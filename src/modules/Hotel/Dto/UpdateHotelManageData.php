<?php

namespace Modules\Hotel\Dto;

use Modules\Hotel\Http\Request\UpdateHotelManageRequest;

readonly class UpdateHotelManageData
{
    /**
     * @param array<string, mixed> $fields
     * @param list<int>|null $galleryIds
     * @param list<int>|null $termIds
     */
    public function __construct(
        public array $fields,
        public bool $hasGallery,
        public ?array $galleryIds,
        public bool $hasTermIds,
        public ?array $termIds,
    ) {
    }

    public static function fromRequest(UpdateHotelManageRequest $request): self
    {
        $data = $request->validated();

        $hasGallery = array_key_exists('gallery', $data);
        $galleryIds = null;

        if ($hasGallery) {
            $galleryIds = array_values(array_map('intval', $data['gallery'] ?? []));
        }

        $hasTermIds = array_key_exists('term_ids', $data);
        $termIds = null;

        if ($hasTermIds) {
            $termIds = array_values(array_map('intval', $data['term_ids'] ?? []));
        }

        unset($data['gallery'], $data['term_ids']);

        if (array_key_exists('has_food', $data)) {
            $data['has_food'] = (bool) $data['has_food'];
        }

        if (array_key_exists('star_rate', $data) && $data['star_rate'] !== null) {
            $data['star_rate'] = (int) $data['star_rate'];
        }

        if (array_key_exists('image_id', $data) && $data['image_id'] !== null) {
            $data['image_id'] = (int) $data['image_id'];
        }

        if (array_key_exists('location_id', $data) && $data['location_id'] !== null) {
            $data['location_id'] = (int) $data['location_id'];
        }

        if (array_key_exists('price', $data) && $data['price'] !== null) {
            $data['price'] = (float) $data['price'];
        }

        return new self(
            fields: $data,
            hasGallery: $hasGallery,
            galleryIds: $galleryIds,
            hasTermIds: $hasTermIds,
            termIds: $termIds,
        );
    }
}
