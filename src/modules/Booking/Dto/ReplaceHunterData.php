<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\ReplaceHunterRequest;

class ReplaceHunterData
{
    public function __construct(
        public int $oldHunterId,
        public int $hunterId,
    ) {}

    public static function fromRequest(ReplaceHunterRequest $request): self
    {
        $data = $request->validated();

        return new self(
            oldHunterId: (int) $data['old_hunter_id'],
            hunterId: (int) $data['hunter_id'],
        );
    }
}
