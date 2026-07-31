<?php

namespace Modules\User\Support;

use Modules\Media\Models\MediaFile;

final class UserAvatarUrl
{
    private const DEFAULT_PATH = 'uploads/0000/1/2026/11/14/avatar.png';

    public static function resolve(int|string|null $avatarId): string
    {
        if ($avatarId === null || $avatarId === '') {
            return asset(self::DEFAULT_PATH);
        }

        $media = MediaFile::query()
            ->whereKey((int) $avatarId)
            ->first(['id', 'file_path', 'driver']);

        return self::fromMediaFile($media);
    }

    public static function fromMediaFile(?MediaFile $media): string
    {
        if (!$media?->file_path) {
            return asset(self::DEFAULT_PATH);
        }

        if (in_array($media->driver, ['s3', 'gcs'], true)) {
            return $media->view_url ?: asset(self::DEFAULT_PATH);
        }

        return asset('uploads/' . ltrim($media->file_path, '/'));
    }
}
