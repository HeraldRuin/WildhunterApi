<?php

namespace Modules\Hotel\Dto;

use Illuminate\Http\Request;

class HotelFilterData
{
    /**
     * @param  list<int>|null  $custom_ids
     */
    public function __construct(
        public ?string $order_by,
        public ?string $order_direction,
        public ?int $limit,
        public ?bool $is_featured = null,
        public ?array $custom_ids = null,
        public ?int $location_id = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        $customIds = null;
        if (array_key_exists('custom_ids', $data) && $data['custom_ids'] !== null) {
            $customIds = array_values(array_filter(array_map('intval', (array) $data['custom_ids'])));
        }

        $isFeatured = null;
        if (array_key_exists('is_featured', $data)) {
            $isFeatured = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return new self(
            order_by: $data['order_by'] ?? null,
            order_direction: $data['order_direction'] ?? null,
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
            is_featured: $isFeatured,
            custom_ids: $customIds,
            location_id: isset($data['location_id']) ? (int) $data['location_id'] : null,
        );
    }
}
