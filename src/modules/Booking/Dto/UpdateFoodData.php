<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\UpdateFoodRequest;

class UpdateFoodData
{
    public function __construct(
        public int $count,
    ) {}

    public static function fromRequest(UpdateFoodRequest $request): self
    {
        $data = $request->validated();

        return new self(
            count: (int) $data['count'],
        );
    }
}
