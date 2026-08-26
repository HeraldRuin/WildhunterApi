<?php

namespace Modules\Animals\Dto;

use Modules\Animals\Requests\AttachAnimalRequest;

readonly class AttachAnimalData
{
    public function __construct(
        public int $animalId,
    ) {}

    public static function fromRequest(AttachAnimalRequest $request): self
    {
        $data = $request->validated();

        return new self(
            animalId: (int) $data['animal_id'],
        );
    }
}
