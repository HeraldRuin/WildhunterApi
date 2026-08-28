<?php

namespace Modules\Attendance\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class AdditionalResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'count' => $this->resource->count !== null
                ? (int) $this->resource->count
                : null,
            'calculation_type' => $this->resource->calculation_type,
            'price' => $this->resource->price !== null
                ? (float) $this->resource->price
                : 0.0,
            'type' => $this->resource->type,
            'can_delete' => !$this->resource->isFood(),
            'can_edit_name' => !$this->resource->isFood(),
        ];
    }
}
