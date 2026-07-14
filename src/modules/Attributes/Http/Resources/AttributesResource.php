<?php

namespace Modules\Attributes\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Terms\Http\Resources\TermsResource;

class AttributesResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'service' => $this->resource->service,
            'position' => $this->resource->position,
            'terms' => TermsResource::collection($this->resource->terms),
        ];
    }
}
