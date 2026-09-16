<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;

class HuntingMethod extends BaseModel
{
    protected $table = 'bc_hunting_methods';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];
}
