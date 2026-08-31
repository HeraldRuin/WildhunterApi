<?php

namespace Modules\Hotel\Dto;

use Modules\Hotel\Http\Request\StoreRoomAvailabilityRequest;

readonly class StoreRoomAvailabilityData
{
    /**
     * @param list<int> $dayOfWeekSelect
     */
    public function __construct(
        public string $startDate,
        public string $endDate,
        public bool $active,
        public ?float $price,
        public int $number,
        public array $dayOfWeekSelect,
        public bool $isInstant,
    ) {
    }

    public static function fromRequest(StoreRoomAvailabilityRequest $request): self
    {
        $data = $request->validated();
        $active = $request->boolean('active');

        $number = $data['number'] ?? null;
        if ($number === null || $number === '') {
            $number = $active ? 1 : 0;
        }

        $dayOfWeekSelect = array_map(
            'intval',
            $data['day_of_week_select'] ?? [],
        );

        return new self(
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            active: $active,
            price: isset($data['price']) ? (float) $data['price'] : null,
            number: (int) $number,
            dayOfWeekSelect: $dayOfWeekSelect,
            isInstant: $request->boolean('is_instant'),
        );
    }
}
