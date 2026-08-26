<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreCollectionTimerRequest;

class StoreCollectionTimerData
{
    public function __construct(
        public string $type,
        public int $timerHours,
    ) {}

    public static function fromRequest(StoreCollectionTimerRequest $request): self
    {
        $data = $request->validated();

        return new self(
            type: $data['type'],
            timerHours: (int) $data['timer_hours'],
        );
    }
}
