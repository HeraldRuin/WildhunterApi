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
    /** @var array<string, int> */
    private const WEBP_QUALITY = [
        'medium' => 75,
        'large' => 82,
    ];

    public function isResizableSize(string $size): bool
    {
        return isset(FileHelper::$defaultSize[$size]);
    }

    public function absoluteOriginalPath(MediaFile $file): string
    {
        $relative = ltrim((string) $file->file_path, '/');
        $uploadsPath = Storage::disk('uploads')->path($relative);

        if (is_file($uploadsPath)) {
            return $uploadsPath;
        }

        return Storage::disk('public')->path('uploads/' . $relative);
    }

    public function cacheRelativePath(MediaFile $file, string $size): string
    {
        [$width, $height] = FileHelper::$defaultSize[$size];
        $relative = ltrim($file->file_path, '/');

        if ($this->servesWebp($size)) {
            $relative = $this->withWebpExtension($relative);
        }

        return 'uploads/_cache/' . $size . '/' . $width . 'x' . $height . '/' . $relative;
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
            ->save($cachePath, quality: $this->qualityFor($size));

        return $cachePath;
    }

    private function servesWebp(string $size): bool
    {
        return isset(self::WEBP_QUALITY[$size]);
    }

    private function qualityFor(string $size): int
    {
        return self::WEBP_QUALITY[$size] ?? 100;
    }

    private function withWebpExtension(string $relative): string
    {
        $directory = pathinfo($relative, PATHINFO_DIRNAME);
        $filename = pathinfo($relative, PATHINFO_FILENAME) . '.webp';

        if ($directory === '' || $directory === '.') {
            return $filename;
        }

        return $directory . '/' . $filename;
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
