<?php

namespace Modules\Location\Models;

use App\Models\BaseModel;

class LocationTranslation extends BaseModel
{
    protected $table = 'bc_location_translations';
    protected $fillable = ['name', 'content','trip_ideas'];

    protected array $cleanFields = [
        'content'
    ];
    protected $casts = [
        'trip_ideas'  => 'array',
    ];
}
