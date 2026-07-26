<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Modules\Media\Helpers\FileHelper;

class BaseModel extends Model
{
    use HasTranslations;

    protected $dateFormat    = 'Y-m-d H:i:s';

    public static function getModelName()
    {

    }
    public function findById($id)
    {
        return $this->find($id);
    }

    public static function getTableName()
    {
        return with(new static)->table;
    }

    public function getImageUrl($size = 'medium'): string
    {
        $url = FileHelper::url($this->image_id, $size);

        return $url ?: '';
    }

    public function getGallery(bool $featuredIncluded = false): array
    {
        if (empty($this->gallery)) {
            return [];
        }

        $listItem = [];

        if ($featuredIncluded && $this->image_id) {
            $listItem[] = [
                'large' => FileHelper::url($this->image_id, 'full'),
                'thumb' => FileHelper::url($this->image_id, 'thumb'),
            ];
        }

        foreach (explode(',', (string) $this->gallery) as $item) {
            $item = trim($item);

            if ($item === '') {
                continue;
            }

            $large = FileHelper::url($item, 'full');
            $thumb = FileHelper::url($item, 'thumb');

            if (!empty($large)) {
                $listItem[] = [
                    'large' => $large,
                    'thumb' => $thumb,
                ];
            }
        }

        return $listItem;
    }
}
