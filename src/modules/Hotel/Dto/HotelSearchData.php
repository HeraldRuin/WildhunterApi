<?php

namespace Modules\Hotel\Dto;

use Illuminate\Http\Request;

class HotelSearchData
{
    public function __construct(
        public ?int $location_id,
        public ?array $locationIds,
        public ?int $animal_id,
        public string $startDate,
        public string $endDate,
        public int $adults,
        public int $children,
        public ?array $star_rate,
        public ?array $price,
        public ?string $order_by,
        public ?string $order_direction,
        public ?int $limit,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            location_id: $data['location_id'] ?? null,
            locationIds: $data['location_ids'] ?? null,
            animal_id: $data['animal_id'] ?? null,
            startDate: $data['check_in'],
            endDate: $data['check_out'],
            adults: $data['adults'] ?? 1,
            children: $data['children'] ?? 0,
            star_rate: $data['star_rate'] ?? null,
            price: $data['price'] ?? null,
            order_by: $data['order_by'] ?? null,
            order_direction: $data['order_direction'] ?? null,
            limit: $data['limit'] ?? null,
        );
    }
}
