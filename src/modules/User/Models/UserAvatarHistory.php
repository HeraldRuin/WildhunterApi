<?php

namespace Modules\User\Models;

use App\Models\BaseModel;
use Modules\Media\Models\MediaFile;

class UserAvatarHistory extends BaseModel
{
    protected $table = 'user_avatar_history';

    protected $fillable = [
        'user_id',
        'media_id',
    ];

    public function media()
    {
        return $this->belongsTo(MediaFile::class, 'media_id');
    }
}
