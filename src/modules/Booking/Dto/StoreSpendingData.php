<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreSpendingRequest;

class StoreSpendingData
{
    public function __construct(
        public float $price,
        public string $comment,
        public int $hunterId,
    ) {}

    public static function fromRequest(StoreSpendingRequest $request): self
    {
        $data = $request->validated();

        return new self(
            price: (float) $data['price'],
            comment: $data['comment'],
            hunterId: (int) $data['hunter_id'],
        );
    }
}
