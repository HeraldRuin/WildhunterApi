<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;

class BookingMarkPaidService
{
    public function __construct(
        private readonly BookingMailService $bookingMailService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {
    }

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

        $this->applyPaid($booking);

        return $booking;
    }

    /**
     * Те же поля и побочные эффекты, что у ручного mark-paid.
     *
     * @param array<string, mixed> $attributes
     */
    public function applyPaid(Booking $booking, array $attributes = []): void
    {
        $wasPaid = $booking->status === Booking::PAID;
        $placesAssigned = $this->allPlacesAssigned($booking, $attributes);

        $attributes['status'] = Booking::PAID;
        $attributes['is_paid'] = true;

        if (!$wasPaid && $placesAssigned) {
            $attributes['is_all_places_assigned'] = true;
        }

        Booking::query()
            ->whereKey($booking->id)
            ->update($attributes);

        foreach ($attributes as $key => $value) {
            $booking->setAttribute($key, $value);
        }

        $this->bookingMailService->sendStatusUpdated($booking);
        BookingUpdatedEvent::dispatchSafely($booking);

        if (!$wasPaid && $placesAssigned) {
            $this->bookingNotificationService->sendHuntScheduled($booking);
        }
    }

    /**
     * Охота без проживания не распределяет койки.
     * Для проживания все принятые и оплатившие должны уже иметь место.
     *
     * @param array<string, mixed> $attributes
     */
    private function allPlacesAssigned(Booking $booking, array $attributes): bool
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return true;
        }

        if (!empty($attributes['is_all_places_assigned']) || $booking->is_all_places_assigned) {
            return true;
        }

        $required = $booking->countAcceptedAndPaidHunters();

        if ($required <= 0) {
            return false;
        }

        return BookingRoomPlace::query()
            ->where('booking_id', $booking->id)
            ->count() === $required;
    }

    private function canMarkPaid(Booking $booking): bool
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return $booking->status === Booking::FINISHED_COLLECTION;
        }

        return $booking->status === Booking::FINISHED_BED;
    }
}
