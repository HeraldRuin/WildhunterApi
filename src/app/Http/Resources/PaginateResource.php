<?php

namespace App\Http\Resources;

class PaginateResource extends BaseJsonResource
{
    public function __construct($resource, private readonly string $resourceClass)
    {
        parent::__construct($resource);
    }
    public function toArray($request): array
    {
        return [
            'items' => $this->resourceClass::collection($this->resource->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'last_page' => $this->resource->lastPage(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ],
        ];
    }
}
