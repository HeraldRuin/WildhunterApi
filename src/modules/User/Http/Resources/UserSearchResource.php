<?php

namespace Modules\User\Http\Resources;

use App\Http\Resources\BaseJsonResource;

class UserSearchResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'user_name' => $this->resource->user_name,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
        ];
    }
}
