<?php

namespace Modules\Terms\Models;

use App\Models\BaseModel;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelTerm;
use Modules\Media\Helpers\FileHelper;
use Modules\Attributes\Models\Attributes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Terms extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bc_terms';
    protected $fillable = [
        'name',
        'content',
        'image_id',
        'icon',
    ];

    public function attribute(): HasOne
    {
        return $this->hasOne(Attributes::class, "id", "attr_id");
    }

    public function getImageUrl($size = "medium")
    {
        $url = FileHelper::url($this->image_id, $size);
        return $url ? $url : '';
    }

    public function hotel(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, HotelTerm::getTableName(), 'term_id', 'target_id');
    }
}
