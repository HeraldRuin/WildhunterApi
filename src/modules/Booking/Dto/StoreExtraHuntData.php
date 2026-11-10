<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreExtraHuntRequest;

class StoreExtraHuntData
{
    /**
     * @param  list<int>  $hunterIds
     */
    public function __construct(
        public int $animalId,
        public array $hunterIds,
    ) {}

    public static function fromRequest(StoreExtraHuntRequest $request): self
    {
        $data = $request->validated();

        return new self(
            animalId: (int) $data['animal_id'],
            hunterIds: array_values(array_unique(array_map('intval', $data['hunter_ids']))),
        );
    }
}
