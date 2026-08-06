<?php

namespace App\Observers;

use Modules\Booking\Models\BookingCounter;

class HotelObserver
{
    public function created($hotel): void
    {
        BookingCounter::firstOrCreate(
            ['hotel_id' => $hotel->id],
            ['last_number' => 0]
        );
    }
}
