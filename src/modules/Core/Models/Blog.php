<?php

namespace Modules\Core\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends BaseModel
{
    use SoftDeletes;

    protected $table = 'core_blogs';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'image_id',
        'excerpt',
        'content_json',
        'author_id',
    ];

    protected $casts = [
        'content_json' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }
}
