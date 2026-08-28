<?php

namespace Modules\Attendance\Dto;

use Modules\Attendance\Http\Requests\UpdateAdditionalRequest;

readonly class UpdateAdditionalData
{
    public function __construct(
        public string $name,
        public float $price,
        public ?int $count,
        public ?string $calculationType,
    ) {}

    public static function fromRequest(UpdateAdditionalRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) $data['name'],
            price: (float) $data['price'],
            count: array_key_exists('count', $data) && $data['count'] !== null && $data['count'] !== ''
                ? (int) $data['count']
                : null,
            calculationType: isset($data['calculation_type']) && $data['calculation_type'] !== ''
                ? (string) $data['calculation_type']
                : null,
        );
    }
}
