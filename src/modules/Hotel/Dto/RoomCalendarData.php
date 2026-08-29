<?php

namespace Modules\Hotel\Dto;

use Modules\Hotel\Http\Request\LoadRoomAvailabilityRequest;

class RoomCalendarData
{
    public function __construct(
        public int|string $id,
        public string $start,
        public string $end,
        public bool $forSingle = false,
    ) {
    }

    public static function fromRequest(LoadRoomAvailabilityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            id: $data['id'],
            start: $data['start'],
            end: $data['end'],
            forSingle: $request->boolean('for_single'),
        );
    }
}
