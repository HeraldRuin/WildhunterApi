<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;

class HotelHuntingMethod extends BaseModel
{
    protected $table = 'bc_hotel_hunting_methods';

    protected $fillable = [
        'hotel_id',
        'hunting_method_id',
    ];

    public $timestamps = false;
}
