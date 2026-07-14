<?php
namespace Modules\Attributes\Models;

use App\Models\BaseModel;

class AttributesTranslation extends BaseModel
{
    protected $table = 'bc_attrs_translations';

    protected $fillable = [
        'name',
    ];
}
