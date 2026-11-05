<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Http\Requests\UpdateFoodRequest;

class UpdateFoodData
{
    public function __construct(
        public int $count,
        public ?int $foodId,
        public ?int $hunterId,
    ) {}

    public static function fromRequest(UpdateFoodRequest $request): self
    {
        $data = $request->validated();

        return new self(
            count: (int) $data['count'],
            foodId: array_key_exists('food_id', $data) ? (int) $data['food_id'] : null,
            hunterId: isset($data['hunter_id']) ? (int) $data['hunter_id'] : null,
        );
    }
}
