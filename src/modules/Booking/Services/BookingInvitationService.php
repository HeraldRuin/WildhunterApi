<?php

namespace Modules\Booking\Services;

use App\Exceptions\NotFoundException;
use App\Models\User;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationService
{
    /**
     * @throws NotFoundException
     */
    public function accept(string $code, User $user): BookingHunterInvitation
    {
        $invitation = $this->findInvitation($code, $user);
        $invitation->status = BookingHunterInvitation::STATUS_ACCEPTED;
        $invitation->accepted_at = now();
        $invitation->declined_at = null;
        $invitation->save();

        return $invitation;
    }

    /**
     * @throws NotFoundException
     */
    public function decline(string $code, User $user): BookingHunterInvitation
    {
        $invitation = $this->findInvitation($code, $user);
        $invitation->status = BookingHunterInvitation::STATUS_DECLINED;
        $invitation->declined_at = now();
        $invitation->save();

        return $invitation;
    }

    /**
     * @throws NotFoundException
     */
    private function findInvitation(string $code, User $user): BookingHunterInvitation
    {
        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $invitation = BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($query) use ($booking) {
                $query->where('booking_id', $booking->id);
            })
            ->where('hunter_id', $user->id)
            ->whereNotIn('status', [
                BookingHunterInvitation::STATUS_DECLINED,
                'removed',
            ])
            ->first();

        if (!$invitation) {
            throw new NotFoundException(
                errorCode: 'booking_invitation_not_found',
                domain: 'booking',
            );
        }

        return $invitation;
    }
}
