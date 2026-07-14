<?php

namespace Modules\Terms\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\TranslationResource;

class TermsResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name'=> $this->resource->name,
            'slug'=> $this->resource->slug,
            'content'=> $this->resource->content,
            'icon'=> $this->resource->icon,
            'image_url' => $this->resource->getImageUrl(),
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
        ];
    }
}
