<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StorePreparationRequest;

class StorePreparationData
{
    public function __construct(
        public int $preparationId,
        public int $animalId,
        public int $count,
    ) {}

    public static function fromRequest(StorePreparationRequest $request): self
    {
        $data = $request->validated();

        return new self(
            preparationId: (int) $data['preparation_id'],
            animalId: (int) $data['animal_id'],
            count: (int) $data['count'],
        );
    }
}
