<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class AnimalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'image_url' => $this->resource->getImageUrl(),
            'content' => $this->resource->content,
            'hunters_count' => (int) ($this->resource->pivot?->hunters_count ?? $this->resource->hunters_count ?? 1),
            'periods' => $this->whenLoaded(
                'periods',
                fn () => AnimalPricePeriodResource::collection($this->resource->periods)
            ),
        ];
    }
}
