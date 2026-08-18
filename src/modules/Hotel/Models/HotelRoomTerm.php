<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;

class HotelRoomTerm extends BaseModel
{
    protected $table = 'bc_hotel_room_term';

    protected $fillable = [
        'term_id',
        'target_id',
    ];
}
