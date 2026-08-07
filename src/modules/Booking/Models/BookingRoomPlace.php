<?php

namespace Modules\Booking\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hotel\Models\HotelRoom;

class BookingRoomPlace extends BaseModel
{
    protected $table = 'bc_booking_room_places';

    protected $fillable = [
        'booking_id',
        'room_index',
        'room_id',
        'place_number',
        'user_id',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
