<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreAdditionalRequest;

class StoreAdditionalData
{
    public function __construct(
        public int $additionalId,
        public string $name,
        public int $count,
        public ?int $hunterId,
    ) {}

    public static function fromRequest(StoreAdditionalRequest $request): self
    {
        $data = $request->validated();

        return new self(
            additionalId: (int) $data['additional_id'],
            name: $data['name'],
            count: (int) $data['count'],
            hunterId: isset($data['hunter_id']) ? (int) $data['hunter_id'] : null,
        );
    }
}
