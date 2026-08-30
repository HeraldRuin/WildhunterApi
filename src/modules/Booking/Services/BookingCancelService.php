<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Hotel\Events\RoomAvailabilityUpdatedEvent;

class BookingCancelService
{
    public function __construct(
        private BookingStatusService $bookingStatusService,
        private BookingMailService $bookingMailService,
        private BookingNotificationService $bookingNotificationService,
    ) {
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     */
    public function cancel(string $code, User $user): Booking
    {
        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $this->ensureCanAccess($booking, $user);
        $this->bookingStatusService->canChangeBookingState($booking);

        Booking::query()
            ->whereKey($booking->id)
            ->update(['status' => Booking::CANCELLED]);

        $booking->status = Booking::CANCELLED;

        $this->bookingNotificationService->sendBookingCancelled($booking, $user);
        $this->cleanupHunterInvitations($booking);

        $this->bookingMailService->sendCancelled($booking);
        BookingUpdatedEvent::dispatchSafely(
            $booking,
            RoomAvailabilityUpdatedEvent::ACTION_CANCELLED,
        );

        return $booking;
    }

    /**
     * @throws ForbiddenException
     */
    private function ensureCanAccess(Booking $booking, User $user): void
    {
        if (is_baseAdmin()) {
            $hotelIds = $user->hotels()->pluck('id');

            if ($hotelIds->isEmpty() || !$hotelIds->contains($booking->hotel_id)) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }

            return;
        }

        if ((int) $booking->customer_id === (int) $user->id
            || (int) $booking->create_user === (int) $user->id) {
            return;
        }

        throw new ForbiddenException(
            errorCode: 'booking_access_denied',
            domain: 'booking',
        );
    }

    private function cleanupHunterInvitations(Booking $booking): void
    {
        $masterIds = $booking->masterHunter()->pluck('id');

        if ($masterIds->isEmpty()) {
            return;
        }

        BookingHunterInvitation::query()
            ->whereIn('booking_hunter_id', $masterIds)
            ->forceDelete();
    }
}
