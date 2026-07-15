<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;

class HotelTerm extends BaseModel
{
    protected $table = 'bc_hotel_term';

    protected $fillable = [
        'term_id',
        'target_id'
    ];
}
