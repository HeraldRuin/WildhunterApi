<?php

namespace Modules\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Media\Models\MediaFile;
use RuntimeException;

class MediaUploadService
{
    public function upload(UploadedFile $file, ?int $userId = null, int $folderId = 0): MediaFile
    {
        $driver = 'uploads';
        $folder = '';

        if ($userId) {
            $folder .= sprintf('%04d', (int) ($userId / 1000)) . '/' . $userId . '/';
        }

        $folder .= date('Y/m/d');

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if ($baseName === '') {
            $baseName = md5($file->getClientOriginalName());
        }

        $extension = $file->getClientOriginalExtension();
        $i = 0;

        do {
            $fileName = $baseName . ($i ?: '');
            $relativePath = $folder . '/' . $fileName . '.' . $extension;
            $i++;
        } while (Storage::disk($driver)->exists($relativePath));

        $storedPath = $file->storePubliclyAs(
            $folder,
            $fileName . '.' . $extension,
            $driver
        );

        if (!$storedPath) {
            throw new RuntimeException('Can not upload file');
        }

        $width = 0;
        $height = 0;

        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $size = @getimagesize($file->getRealPath());
            if ($size) {
                [$width, $height] = $size;
            }
        }

        try {
            $media = new MediaFile();
            $media->file_name = $fileName;
            $media->file_path = $storedPath;
            $media->file_size = $file->getSize();
            $media->file_type = $file->getMimeType();
            $media->file_extension = $extension;
            $media->folder_id = $folderId;
            $media->file_width = $width;
            $media->file_height = $height;
            $media->driver = $driver;
            $media->author_id = $userId;
            $media->create_user = $userId;
            $media->is_private = 0;
            $media->save();

            return $media;
        } catch (\Throwable $exception) {
            Storage::disk($driver)->delete($storedPath);
            throw $exception;
        }
    }
}
