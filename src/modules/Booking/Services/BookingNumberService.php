<?php

namespace Modules\Booking\Services;

use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingCounter;

class BookingNumberService
{
    /**
     * @throws NotFoundException
     */
    public function generate(int $hotelId): int
    {
        return DB::transaction(function () use ($hotelId) {
            $counter = BookingCounter::where('hotel_id', $hotelId)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                throw new NotFoundException(
                    message: __('booking.errors.booking_counter_not_found'),
                    errorCode: 'booking_counter_not_found',
                    domain: 'booking',
                );
            }

            $counter->increment('last_number');

            return $counter->last_number;
        });
    }
}
