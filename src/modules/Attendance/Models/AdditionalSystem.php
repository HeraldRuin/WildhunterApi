<?php

namespace Modules\Attendance\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class AdditionalSystem extends BaseModel
{
    protected $table = 'bc_additional_systems';

    protected $fillable = [
        'user_id',
        'name',
    ];
}
