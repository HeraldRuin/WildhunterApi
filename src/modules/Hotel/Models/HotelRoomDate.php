<?php
namespace Modules\Hotel\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;

class HotelRoomDate extends BaseModel
{
    protected $table = 'bc_hotel_room_dates';

    protected $casts = [
        'person_types'=>'array'
    ];

    public static function getDatesInRanges($start_date,$end_date): Collection
    {
        return static::query()->where([
            ['start_date','>=',$start_date],
            ['end_date','<=',$end_date],
        ])->take(100)->get();
    }
}
