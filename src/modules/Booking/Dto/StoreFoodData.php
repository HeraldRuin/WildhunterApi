<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\StoreFoodRequest;

class StoreFoodData
{
    public function __construct(
        public int $count,
    ) {}

    public static function fromRequest(StoreFoodRequest $request): self
    {
        $data = $request->validated();

        return new self(
            count: (int) $data['count'],
        );
    }
}
