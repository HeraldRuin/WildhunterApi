<?php

namespace Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use Modules\Media\Models\MediaFile;
use Modules\Media\Services\MediaImageService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaImageController extends Controller
{
    public function __construct(
        private readonly MediaImageService $mediaImageService,
    ) {
    }

    public function show(int $id, string $size): BinaryFileResponse
    {
        if (!$this->mediaImageService->isResizableSize($size)) {
            abort(404);
        }

        $file = MediaFile::query()->find($id);

        if (!$file) {
            throw new NotFoundHttpException('Media file not found.');
        }

        try {
            $path = $this->mediaImageService->ensureResized($file, $size);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Throwable) {

            $original = $this->mediaImageService->absoluteOriginalPath($file);

            if (!is_file($original)) {
                abort(404);
            }

            return $this->fileResponse($original);
        }

        return $this->fileResponse($path);
    }

    private function fileResponse(string $path): BinaryFileResponse
    {
        return response()->file($path, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
