<?php

namespace Modules\Animals\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class ManagedAnimalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        $huntersCount = (int) ($this->resource->hunters_count ?? 1);
        $huntersCount = $huntersCount > 0 ? $huntersCount : 1;
        $maxHuntersCount = (int) ($this->resource->max_hunters_count ?? 0);

        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'hunters_count' => $huntersCount,
            'max_hunters_count' => $maxHuntersCount > 0 ? $maxHuntersCount : $huntersCount,
        ];
    }
}
