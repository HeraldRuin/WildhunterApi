<?php

namespace Modules\Animals\Dto;

use Modules\Animals\Requests\UpdateHuntersCountRequest;

readonly class UpdateHuntersCountData
{
    public function __construct(
        public int $huntersCount,
        public int $maxHuntersCount,
    ) {}

    public static function fromRequest(UpdateHuntersCountRequest $request): self
    {
        $data = $request->validated();

        return new self(
            huntersCount: (int) $data['hunters_count'],
            maxHuntersCount: (int) $data['max_hunters_count'],
        );
    }
}
