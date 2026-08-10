<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Events\BookingHistoryUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationService
{
    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function invite(string $code, int $hunterId, User $actor): BookingHunterInvitation
    {
        return DB::transaction(function () use ($code, $hunterId, $actor): BookingHunterInvitation {
            $booking = Booking::query()
                ->where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new NotFoundException(
                    errorCode: 'booking_not_found',
                    domain: 'booking',
                );
            }

            $masterHunter = $this->ensureMasterHunter($booking, $actor);

            if ($booking->status !== Booking::START_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_gathering_not_started',
                    domain: 'booking',
                );
            }

            $hunter = User::query()->find($hunterId);

            if (!$hunter) {
                throw new NotFoundException(
                    errorCode: 'user_not_found',
                    domain: 'booking',
                );
            }

            $hasActiveInvitation = BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where('hunter_id', $hunter->id)
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhereNotIn('status', [
                            BookingHunterInvitation::STATUS_DECLINED,
                            'removed',
                        ]);
                })
                ->exists();

            if ($hasActiveInvitation) {
                throw new ConflictException(
                    errorCode: 'hunter_already_in_booking',
                    domain: 'booking',
                );
            }

            $invitation = BookingHunterInvitation::query()->updateOrCreate(
                [
                    'booking_hunter_id' => $masterHunter->id,
                    'hunter_id' => $hunter->id,
                ],
                [
                    'email' => $hunter->email,
                    'invited' => true,
                    'status' => BookingHunterInvitation::STATUS_PENDING,
                    'invited_at' => now(),
                    'accepted_at' => null,
                    'declined_at' => null,
                    'invitation_token' => "{$booking->code}-{$hunter->id}",
                ],
            );

            event(new BookingHistoryUpdatedEvent(
                $booking,
                $hunter->id,
                BookingHistoryUpdatedEvent::ACTION_ADDED,
            ));

            return $invitation;
        });
    }

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

    /**
     * @throws ForbiddenException
     */
    private function ensureMasterHunter(Booking $booking, User $user): BookingHunter
    {
        $masterHunter = $booking->masterHunter()
            ->where('invited_by', $user->id)
            ->first();

        if (!$masterHunter) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        return $masterHunter;
    }
}
