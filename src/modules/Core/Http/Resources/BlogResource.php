<?php

namespace Modules\Core\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Core\Models\Blog;

class BlogResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var Blog $blog */
        $blog = $this->resource;

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'status' => $blog->status,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content_json,
            'image_url' => $blog->getImageUrl() ?: null,
            'author_id' => $blog->author_id,
            'created_at' => $blog->created_at,
            'updated_at' => $blog->updated_at,
        ];
    }
}
