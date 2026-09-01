<?php

namespace Modules\Attendance\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class SystemServiceResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}
