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
    ) {}

    public static function fromRequest(CheckAvailabilityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            hotelId: (int) $data['hotel_id'],
            checkIn: $data['check_in'],
            checkOut: $data['check_out'],
            adults: (int) $data['adults'],
        );
    }

    public function toFilters(): array
    {
        return [
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'adults' => $this->adults,
        ];
    }
}
