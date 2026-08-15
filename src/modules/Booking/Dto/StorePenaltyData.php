<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StorePenaltyRequest;

class StorePenaltyData
{
    public function __construct(
        public int $penaltyId,
        public int $hunterId,
        public int $animalId,
        public string $type,
    ) {}

    public static function fromRequest(StorePenaltyRequest $request): self
    {
        $data = $request->validated();

        return new self(
            penaltyId: (int) $data['penalty_id'],
            hunterId: (int) $data['hunter_id'],
            animalId: (int) $data['animal_id'],
            type: $data['type'],
        );
    }
}
