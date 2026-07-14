<?php

namespace Modules\Attributes\Dto;

use Illuminate\Foundation\Http\FormRequest;

class AttributesServiceData
{
    public function __construct(
        public ?string $type,
    )
    {}

    public static function fromRequest(FormRequest $request): static
    {
        $data = $request->validated();

        return new self(
            type: $data['type'] ?? null,
        );
    }
}
