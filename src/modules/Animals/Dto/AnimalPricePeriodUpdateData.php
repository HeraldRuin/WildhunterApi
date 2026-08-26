<?php

namespace Modules\Animals\Dto;

use Modules\Animals\Requests\UpdateAnimalPricePeriodRequest;

readonly class AnimalPricePeriodUpdateData
{
    public function __construct(
        public string $startDate,
        public string $endDate,
        public float $price,
    ) {}

    public static function fromRequest(UpdateAnimalPricePeriodRequest $request): self
    {
        $data = $request->validated();

        return new self(
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            price: (float) $data['amount'],
        );
    }
}
