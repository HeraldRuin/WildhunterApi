<?php

namespace Modules\Core\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Resources\BlogResource;
use Modules\Core\Services\BlogService;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blogService)
    {
    }

    public function index(): JsonResponse
    {
        $blogs = $this->blogService->listPublished();

        return new SuccessResponse(data: BlogResource::collection($blogs));
    }
}
