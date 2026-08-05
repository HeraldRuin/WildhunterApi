<?php

namespace Modules\Booking\Services;

use Modules\Booking\Models\Booking;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;

class BookingCheckoutService
{
    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws ValidationException
     */
    public function findForCheckout(string $code, int $userId): Booking
    {
        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking'
            );
        }

        if ((int) $booking->customer_id !== $userId) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking'
            );
        }

        if (!in_array($booking->status, [Booking::DRAFT, Booking::UNPAID], true)) {
            throw new ValidationException(
                message: __('booking.errors.booking_not_confirmable'),
                errorCode: 'booking_not_confirmable',
                domain: 'booking'
            );
        }

        return $booking;
    }
}
