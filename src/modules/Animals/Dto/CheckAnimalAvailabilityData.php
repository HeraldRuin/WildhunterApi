<?php

namespace Modules\Animals\Dto;

use Modules\Animals\Http\Request\CheckAnimalAvailabilityRequest;

class CheckAnimalAvailabilityData
{
    public function __construct(
        public int $hotelId,
        public int $animalId,
        public string $hunterData,
        public int $hunters,
        public ?string $checkIn,
        public ?string $checkOut,
    ) {}

    public static function fromRequest(CheckAnimalAvailabilityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            hotelId: (int) $data['hotel_id'],
            animalId: (int) $data['animal_id'],
            hunterData: $data['hunter_data'],
            hunters: (int) $data['hunters'],
            checkIn: $data['check_in'] ?? null,
            checkOut: $data['check_out'] ?? null,
        );
    }
}
