<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Blog;

class BlogService
{
    public function listPublished(): Collection
    {
        return Blog::published()
            ->orderByDesc('created_at')
            ->get();
    }

    public function findPublishedBySlug(string $slug): ?Blog
    {
        return Blog::published()
            ->where('slug', $slug)
            ->first();
    }
}
