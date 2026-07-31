<?php

namespace Modules\User\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\User\Support\UserAvatarUrl;

class AvatarHistoryResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'url' => UserAvatarUrl::fromMediaFile($this->resource),
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
