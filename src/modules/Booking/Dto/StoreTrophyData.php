<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreTrophyRequest;

class StoreTrophyData
{
    public function __construct(
        public int $trophyId,
        public int $animalId,
        public string $type,
        public int $count,
        /** @var list<int> */
        public array $hunterIds,
    ) {}

    public static function fromRequest(StoreTrophyRequest $request): self
    {
        $data = $request->validated();

        return new self(
            trophyId: (int) $data['trophy_id'],
            animalId: (int) $data['animal_id'],
            type: $data['type'],
            count: (int) $data['count'],
            hunterIds: array_values(array_unique(array_map('intval', $data['hunter_ids']))),
        );
    }
}
