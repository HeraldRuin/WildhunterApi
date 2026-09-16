<?php

namespace Modules\Hotel\Dto;

use Modules\Hotel\Http\Request\CalendarAvailabilityRequest;

class CalendarAvailabilityData
{
    public function __construct(
        public int $hotelId,
        public string $start,
        public string $end,
        public ?int $adults,
    ) {
    }

    public static function fromRequest(CalendarAvailabilityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            hotelId: (int) $data['hotel_id'],
            start: $data['start'],
            end: $data['end'],
            adults: isset($data['adults']) ? (int) $data['adults'] : null,
        );
    }
}
