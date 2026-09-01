<?php

namespace Modules\Attendance\Dto;

use Modules\Attendance\Http\Requests\StoreAdditionalRequest;

readonly class StoreAdditionalData
{
    public function __construct(
        public string $name,
        public float $price,
        public bool $isSystem,
    ) {}

    public static function fromRequest(StoreAdditionalRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) $data['name'],
            price: (float) $data['price'],
            isSystem: (bool) $data['is_system'],
        );
    }
}
