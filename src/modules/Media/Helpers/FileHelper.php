<?php

namespace Modules\Media\Helpers;

use Illuminate\Support\Facades\Cache;
use Modules\Media\Models\MediaFile;

class FileHelper
{
    public static array $defaultSize = [
        'thumb' => [
            300,
            300
        ],
        'medium' => [
            600,
            600
        ],
        'large' => [
            1024,
            1024
        ],
        'max_large' => [
            2500,
            2500
        ],
    ];


    public static function list_size(): array
    {
        $sizes = [];
        foreach (self::$defaultSize as $size) {
            $sizes[] = $size[0];
        }
        return $sizes;
    }

    public static function urlCacheKey(int|string $fileId, string $size = 'medium'): string
    {
        return 'media_file_view_url:' . $fileId . ':' . $size;
    }

    public static function forgetUrlCache(int|string|null $fileId): void
    {
        if ($fileId === null || $fileId === '') {
            return;
        }

        foreach (array_keys(self::$defaultSize) as $size) {
            Cache::forget(self::urlCacheKey($fileId, $size));
        }

        Cache::forget(self::urlCacheKey($fileId, 'full'));
    }

    /**
     * Прямая ссылка на файл в uploads/ (без /media/{id}/resize).
     * Для аватаров и других файлов, где нужен оригинал по file_path.
     */
    public static function directUploadUrl(int|string|null $fileId): string|false
    {
        if ($fileId === null || $fileId === '') {
            return false;
        }

        return self::url($fileId, 'full', false);
    }

    public static function url($fileId, $size = 'medium', $resize = true)
    {
        if ($fileId instanceof MediaFile) {
            $file = $fileId;
            $fileId = $file->id;
        } else {
            if (empty($fileId)) {
                return false;
            }

            $file = null;
        }

        $shouldResize = $resize && isset(self::$defaultSize[$size]);

        if (!$shouldResize) {
            $file ??= MediaFile::find($fileId);

            return $file?->view_url ?: false;
        }

        $cacheKey = self::urlCacheKey($fileId, $size);

        $url = Cache::remember($cacheKey, now()->addDay(), function () use ($fileId, $size) {
            $file = MediaFile::find($fileId);

            if (!$file) {
                return '';
            }

            if (in_array($file->driver, ['s3', 'gcs'], true)) {
                return $file->view_url ?: '';
            }

            return rtrim((string) config('app.url'), '/') . route('media.image', [
                'id' => $file->id,
                'size' => $size,
            ], absolute: false);
        });

        return $url !== '' ? $url : false;
    }
}
