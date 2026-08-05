<?php

namespace Modules\Hotel\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Modules\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotelRoomBooking extends BaseModel
{
    protected $table = 'bc_hotel_room_bookings';

    protected $fillable = [
        'room_id',
        'parent_id',
        'start_date',
        'end_date',
        'number',
        'booking_id',
        'price',
    ];

    public function scopeInRange($query,$start,$end): void
    {
        $query->where('bc_hotel_room_bookings.start_date','<=',$end)->where('bc_hotel_room_bookings.end_date','>',$start);
    }

    public function scopeActive($query)
    {
        return $query->join('bc_bookings', function ($join) {
            $join->on('bc_bookings.id', '=', $this->table . '.booking_id');
        })->whereNotIn('bc_bookings.status', Booking::$notAcceptedStatus)->where('bc_bookings.deleted_at', null);
    }

    public function room(): HasOne
    {
        return $this->hasOne(HotelRoom::class,'id','room_id')->withDefault();
    }
    public function booking(): BelongsTo
    {
    	return $this->belongsTo(Booking::class,'booking_id');
    }

    public static function getByBookingId($id): Collection
    {
        return parent::query()->where([
            'booking_id'=>$id
        ])->get();
    }
}
