<?php

namespace Modules\Hotel\Dto;

use Illuminate\Http\Request;

class HotelSearchData
{
    /**
     * @param  list<int>|null  $locationIds
     * @param  list<int>|null  $animalIds
     */
    public function __construct(
        public ?int $location_id,
        public ?array $locationIds,
        public ?int $animal_id,
        public ?array $animalIds,
        public string $startDate,
        public string $endDate,
        public int $adults,
        public int $children,
        public ?array $star_rate,
        public ?array $price,
        public ?array $termIds,
        public ?string $sort,
        public ?string $order_by,
        public ?string $order_direction,
        public ?int $limit,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        $locationIds = null;
        if (array_key_exists('location_ids', $data) && $data['location_ids'] !== null) {
            $locationIds = array_values(array_map('intval', $data['location_ids']));
        }

        $animalIds = null;
        if (array_key_exists('animal_ids', $data) && $data['animal_ids'] !== null) {
            $animalIds = array_values(array_map('intval', $data['animal_ids']));
        }

        return new self(
            location_id: isset($data['location_id']) ? (int) $data['location_id'] : null,
            locationIds: $locationIds,
            animal_id: isset($data['animal_id']) ? (int) $data['animal_id'] : null,
            animalIds: $animalIds,
            startDate: $data['check_in'],
            endDate: $data['check_out'],
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            star_rate: $data['star_rate'] ?? null,
            price: $data['price'] ?? null,
            termIds: $data['term_ids'] ?? null,
            sort: $data['sort'] ?? $request->input('sort'),
            order_by: $data['order_by'] ?? $request->input('order_by'),
            order_direction: $data['order_direction'] ?? $request->input('order_direction'),
            limit: $data['limit'] ?? null,
        );
    }
}
