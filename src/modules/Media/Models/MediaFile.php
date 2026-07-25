<?php

namespace Modules\Media\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Services\MediaImageService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MediaFile extends BaseModel
{
    use SoftDeletes;

    protected $table = 'media_files';

    protected static function booted(): void
    {
        static::saved(function (MediaFile $file) {
            $file->forgetCachedData();
        });

        static::deleted(function (MediaFile $file) {
            $file->forgetCachedData();
        });
    }

    public static function findMediaByName($name)
    {
        return MediaFile::where("file_name", $name)->first();
    }

    public function cacheKey(): string
    {
        return sprintf("%s/%s", $this->getTable(), $this->getKey());
    }

    public function forgetCachedData(): void
    {
        FileHelper::forgetUrlCache($this->getKey());
        Cache::forget($this->cacheKey() . ':' . $this->getKey());

        app(MediaImageService::class)->forgetCache($this);
    }

    public function scopeInFolder($query, $folder_id){
        return $query->where('folder_id', $folder_id);
    }

    public function forceDelete(): bool
    {
        $this->forgetCachedData();

        return parent::forceDelete();
    }

    public function viewUrl(): Attribute
    {
        return Attribute::make(
            get:function(){
                return match ($this->driver) {
                    "s3", "gcs" => $this->generateUrl($this->file_path),
                    default => asset('uploads/' . $this->file_path),
                };
            }
        );
    }

    public function generateUrl($file_path, int $mins = 24 * 60): string
    {
        return Storage::disk($this->driver)->temporaryUrl(
            $file_path, now()->addMinutes($mins)
        );
    }

    public function download($name = '',$headers = [])
    {
        return Storage::disk($this->driver)->download($this->file_path,$name,$headers);
    }
}
