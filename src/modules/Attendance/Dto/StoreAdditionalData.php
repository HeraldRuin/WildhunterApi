<?php

namespace Modules\Attendance\Dto;

use Modules\Attendance\Http\Requests\StoreAdditionalRequest;

readonly class StoreAdditionalData
{
    public function __construct(
        public string $name,
        public ?float $price,
        public ?int $count,
        public ?string $calculationType,
        public bool $isSystem,
    ) {}

    public static function fromRequest(StoreAdditionalRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) $data['name'],
            price: array_key_exists('price', $data) && $data['price'] !== null && $data['price'] !== ''
                ? (float) $data['price']
                : null,
            count: array_key_exists('count', $data) && $data['count'] !== null && $data['count'] !== ''
                ? (int) $data['count']
                : null,
            calculationType: isset($data['calculation_type']) && $data['calculation_type'] !== ''
                ? (string) $data['calculation_type']
                : null,
            isSystem: (bool) $data['is_system'],
        );
    }
}
