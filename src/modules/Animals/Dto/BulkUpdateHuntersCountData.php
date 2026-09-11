<?php

namespace Modules\Animals\Dto;

use Modules\Animals\Requests\BulkUpdateHuntersCountRequest;

readonly class BulkUpdateHuntersCountData
{
    /**
     * @param list<array{id: int, huntersCount: int}> $animals
     */
    public function __construct(
        public array $animals,
    ) {}

    public static function fromRequest(BulkUpdateHuntersCountRequest $request): self
    {
        $data = $request->validated();

        $animals = array_map(
            static fn (array $item): array => [
                'id' => (int) $item['id'],
                'huntersCount' => (int) $item['hunters_count'],
            ],
            $data['animals'],
        );

        return new self(animals: $animals);
    }
}
