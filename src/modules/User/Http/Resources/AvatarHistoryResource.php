<?php

namespace Modules\User\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Media\Helpers\FileHelper;

class AvatarHistoryResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'url' => FileHelper::url($this->resource->id, 'full', false) ?: $this->resource->view_url,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}
