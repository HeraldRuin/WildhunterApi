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
            foreach (['Завтрак', 'Обед', 'Ужин'] as $mealName) {
                AddetionalPrice::query()->firstOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'type' => AddetionalPrice::FOOD,
                        'name' => $mealName,
                    ],
                    [
                        'user_id' => $authUser->id,
                        'price' => 0,
                        'is_visible' => true,
                        'is_system' => false,
                        'calculation_type' => AddetionalPrice::PERSON,
                    ]
                );
            }
        }

        BookingCounter::firstOrCreate(
            ['hotel_id' => $hotel->id],
            ['last_number' => 0]
        );
    }
}
