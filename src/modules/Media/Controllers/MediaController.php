<?php

namespace Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Media\Http\Requests\StoreMediaRequest;
use Modules\Media\Http\Resources\MediaFileResource;
use Modules\Media\Services\MediaUploadService;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaUploadService $mediaUploadService,
    ) {
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = $this->mediaUploadService->upload(
            $request->file('file'),
            Auth::id() !== null ? (int) Auth::id() : null,
            0,
            'hotels',
        );

        return new SuccessResponse(
            code: 'media_uploaded',
            domain: 'media',
            data: new MediaFileResource($media),
        );
    }
}
