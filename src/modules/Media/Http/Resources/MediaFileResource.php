<?php

namespace Modules\Media\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Models\MediaFile;

class MediaFileResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var MediaFile $media */
        $media = $this->resource;

        return [
            'id' => $media->id,
            'large' => FileHelper::url($media->id, 'full') ?: $media->view_url,
            'medium' => FileHelper::url($media->id, 'medium') ?: $media->view_url,
            'thumb' => FileHelper::url($media->id, 'thumb') ?: $media->view_url,
        ];
    }
}
