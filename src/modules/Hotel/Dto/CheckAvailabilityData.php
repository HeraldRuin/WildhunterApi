<?php

namespace Modules\Hotel\Dto;

use Modules\Hotel\Http\Request\CheckAvailabilityRequest;

class CheckAvailabilityData
{
    public function __construct(
        public int $hotelId,
        public string $checkIn,
        public string $checkOut,
        public int $adults,
        public ?string $hunterData,
        public ?int $hunters,
    ) {}

    public static function fromRequest(CheckAvailabilityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            hotelId: (int) $data['hotel_id'],
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            adults: (int) $data['adults'],
            hunterData: $data['hunter_data'] ?? null,
            hunters: isset($data['hunters']) ? (int) $data['hunters'] : null,
        );
    }

    public function toFilters(): array
    {
        return [
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'adults' => $this->adults,
            'hunter_data' => $this->hunterData,
            'hunters' => $this->hunters,
        ];
    }
}
