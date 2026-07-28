<?php

namespace Modules\Media\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Models\MediaFile;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaImageService
{
    public function isResizableSize(string $size): bool
    {
        return isset(FileHelper::$defaultSize[$size]);
    }

    public function absoluteOriginalPath(MediaFile $file): string
    {
        return Storage::disk('public')->path('uploads/' . ltrim($file->file_path, '/'));
    }

    public function cacheRelativePath(MediaFile $file, string $size): string
    {
        [$width, $height] = FileHelper::$defaultSize[$size];

        return 'uploads/_cache/' . $size . '/' . $width . 'x' . $height . '/' . ltrim($file->file_path, '/');
    }

    public function ensureResized(MediaFile $file, string $size): string
    {
        if (!$this->isResizableSize($size)) {
            throw new RuntimeException("Unsupported image size [{$size}]");
        }

        if (in_array($file->driver, ['s3', 'gcs'], true)) {
            throw new RuntimeException('On-the-fly resize is only supported for local media files.');
        }

        $cacheRelative = $this->cacheRelativePath($file, $size);
        $disk = Storage::disk('public');

        if ($disk->exists($cacheRelative)) {
            return $disk->path($cacheRelative);
        }

        $originalPath = $this->absoluteOriginalPath($file);

        if (!is_file($originalPath)) {
            throw new NotFoundHttpException('Original media file not found.');
        }

        [$width, $height] = FileHelper::$defaultSize[$size];
        $cachePath = $disk->path($cacheRelative);

        $disk->makeDirectory(dirname($cacheRelative));

        Image::decode($originalPath)
            ->scaleDown($width, $height)
            ->save($cachePath, quality: 100);

        return $cachePath;
    }

    public function forgetCache(MediaFile $file): void
    {
        $disk = Storage::disk('public');

        foreach (array_keys(FileHelper::$defaultSize) as $size) {
            $relative = $this->cacheRelativePath($file, $size);

            if ($disk->exists($relative)) {
                $disk->delete($relative);
            }
        }
    }
}
