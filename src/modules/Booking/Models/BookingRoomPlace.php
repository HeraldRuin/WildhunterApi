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

    /**
     * Занятые места в том же виде, что GET /bookings/{code}/places → data.places.
     *
     * @return array<int|string, mixed>
     */
    public static function occupiedMap(int $bookingId): array
    {
        return self::query()
            ->with('user:id,first_name,last_name,user_name')
            ->where('booking_id', $bookingId)
            ->get()
            ->groupBy(['room_index', 'room_id', 'place_number'])
            ->toArray();
    }
}
