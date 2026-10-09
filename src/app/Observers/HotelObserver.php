<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\BookingCounter;

class HotelObserver
{
    public function created($hotel): void
    {
        $authUser = Auth::user();

        if ($authUser && $authUser->hasRole('baseadmin') && $hotel->id) {
            AddetionalPrice::query()
                ->where('type', AddetionalPrice::FOOD)
                ->update(['hotel_id' => $hotel->id]);
        }

        BookingCounter::firstOrCreate(
            ['hotel_id' => $hotel->id],
            ['last_number' => 0]
        );
    }
}
