<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\SelectPlaceRequest;

class SelectPlaceData
{
    public function __construct(
        public int $roomId,
        public int $placeNumber,
        public int $roomIndex,
        public int $userId,
    ) {}

    public static function fromRequest(SelectPlaceRequest $request): self
    {
        $data = $request->validated();

        return new self(
            roomId: (int) $data['room_id'],
            placeNumber: (int) $data['place_number'],
            roomIndex: (int) $data['room_index'],
            userId: (int) $request->user()->id,
        );
    }
}
