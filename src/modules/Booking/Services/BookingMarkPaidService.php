<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;

class BookingMarkPaidService
{
    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function markPaid(string $code, User $user): Booking
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

        if (!$this->canMarkPaid($booking)) {
            throw new ConflictException(
                errorCode: 'booking_not_markable_as_paid',
                domain: 'booking',
            );
        }

        Booking::query()
            ->whereKey($booking->id)
            ->update([
                'status' => Booking::PAID,
                'is_paid' => true,
            ]);

        $booking->status = Booking::PAID;
        $booking->is_paid = true;

        event(new BookingUpdatedEvent($booking));

        return $booking;
    }

    private function canMarkPaid(Booking $booking): bool
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return $booking->status === Booking::FINISHED_COLLECTION;
        }

        return $booking->status === Booking::FINISHED_BED;
    }
}
