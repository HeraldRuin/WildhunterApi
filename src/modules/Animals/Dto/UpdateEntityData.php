<?php

namespace Modules\Animals\Dto;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Animals\Requests\UpdateEntityRequest;

class UpdateEntityData
{
    public function __construct(
        public string $type,
        public int $id,
        public ?float $price,
    ) {}

    public static function fromRequest(UpdateEntityRequest $request): self
    {
        $data = $request->validated();

        return new self(
            type: $data['type'],
            id: (int) $data['id'],
            price: array_key_exists('price', $data) && $data['price'] !== null
                ? (float) $data['price']
                : null,
        );
    }

    public function getEntity(): Model
    {
        return match ($this->type) {
            Animal::SERVICE_PREPARATIONS => AnimalPreparation::query()->findOrFail($this->id),
            Animal::SERVICE_TROPHIES => AnimalTrophy::query()->findOrFail($this->id),
            Animal::SERVICE_FINES => AnimalFine::query()->findOrFail($this->id),
            default => throw new InvalidArgumentException("Unknown entity type: {$this->type}"),
        };
    }
}
