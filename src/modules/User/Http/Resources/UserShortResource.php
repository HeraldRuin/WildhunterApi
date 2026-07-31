<?php

namespace Modules\User\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\User\Support\UserAvatarUrl;

class UserShortResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'is_guest' => $this->resource->is_guest ?? false,
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'nik' => $this->resource->user_name,
            'avatar_url' => UserAvatarUrl::resolve($this->resource->avatar_id),
            'bio' => $this->resource->bio,
        ];
    }
}
