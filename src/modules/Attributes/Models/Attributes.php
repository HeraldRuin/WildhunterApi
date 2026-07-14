<?php

namespace Modules\Attributes\Models;

use App\Models\BaseModel;
use Modules\Terms\Models\Terms;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attributes extends BaseModel
{
    use SoftDeletes;

    protected $translation_class = AttributesTranslation::class;

    protected $table = 'bc_attrs';

    protected $fillable = ['name','display_type','hide_in_single','hide_in_filter_search','position'];

    public function terms()
    {
        return $this->hasMany(Terms::class, 'attr_id', 'id')->with(['translation']);
    }
}
