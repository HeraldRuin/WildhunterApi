<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreFoodRequest;

class StoreFoodData
{
    public function __construct(
        public int $foodId,
        public int $count,
    ) {}

    public static function fromRequest(StoreFoodRequest $request): self
    {
        $data = $request->validated();

        return new self(
            foodId: (int) $data['food_id'],
            count: (int) $data['count'],
        );
    }
}
