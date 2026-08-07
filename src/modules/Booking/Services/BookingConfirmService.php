<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Booking\Models\Booking;

class BookingConfirmService
{
    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function confirm(string $code, User $user): Booking
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $hotelIds = $user->hotels()->pluck('id');

        if ($hotelIds->isEmpty() || !$hotelIds->contains($booking->hotel_id)) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        if ($booking->status !== Booking::PROCESSING) {
            throw new ConflictException(
                errorCode: 'booking_not_confirmable',
                domain: 'booking',
            );
        }

        Booking::query()
            ->whereKey($booking->id)
            ->update(['status' => Booking::CONFIRMED]);

        $booking->status = Booking::CONFIRMED;

        return $booking;
    }
}
