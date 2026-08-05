<?php

namespace Modules\Booking\Models;

use App\Models\BaseModel;
use Modules\Hotel\Models\HotelRoom;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookedDay extends BaseModel
{
    protected $table = 'bc_booked_days';

    protected $fillable = [
        'booking_id',
        'room_id',
        'date',
        'number',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }
}
