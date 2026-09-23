<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class AnimalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        $huntersCount = (int) ($this->resource->pivot?->hunters_count ?? $this->resource->hunters_count ?? 1);
        $huntersCount = $huntersCount > 0 ? $huntersCount : 1;
        $maxHuntersCount = (int) ($this->resource->pivot?->max_hunters_count ?? $this->resource->max_hunters_count ?? 0);

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'image_url' => $this->resource->getImageUrl(),
            'content' => $this->resource->content,
            'hunters_count' => $huntersCount,
            'max_hunters_count' => $maxHuntersCount > 0 ? $maxHuntersCount : $huntersCount,
            'hunt_type' => $this->huntType(),
            'periods' => $this->whenLoaded(
                'periods',
                fn () => AnimalPricePeriodResource::collection($this->resource->periods)
            ),
        ];
    }

    /**
     * @return array{code: string, title: string}
     */
    private function huntType(): array
    {
        $code = $this->resource->huntTypeCode();

        return [
            'code' => $code,
            'title' => __('animal.hunt_types.'.$code),
        ];
    }
}
