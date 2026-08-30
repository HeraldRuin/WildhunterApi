<?php

namespace Modules\Hotel\Dto;

use Illuminate\Foundation\Http\FormRequest;

readonly class UpdateRoomManageData
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

    public static function fromRequest(FormRequest $request): self
    {
        return self::fromValidated($request->validated());
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromValidated(array $data): self
    {
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

        foreach (['image_id', 'number', 'beds', 'size', 'adults', 'children', 'min_day_stays'] as $intField) {
            if (array_key_exists($intField, $data) && $data[$intField] !== null) {
                $data[$intField] = (int) $data[$intField];
            }
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
