<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Dto\ReplaceHunterData;
use Modules\Booking\Dto\ReplaceHunterResultData;
use Modules\Booking\Events\BookingHistoryUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationService
{
    public function __construct(
        private readonly BookingCollectionService $bookingCollectionService,
    ) {
    }

    public function remove(string $code, int $hunterId, User $actor): void
    {
        DB::transaction(function () use ($code, $hunterId, $actor): void {
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

            if (!in_array($booking->status, [
                Booking::FINISHED_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
            ], true)) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_remove_not_allowed',
                    domain: 'booking',
                );
            }

            if ($hunterId === (int) $masterHunter->invited_by) {
                throw new ConflictException(
                    errorCode: 'master_hunter_cannot_be_removed',
                    domain: 'booking',
                );
            }

            $invitation = BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where('hunter_id', $hunterId)
                ->lockForUpdate()
                ->first();

            if (!$invitation) {
                throw new NotFoundException(
                    errorCode: 'booking_invitation_not_found',
                    domain: 'booking',
                );
            }

            if ((bool) $invitation->prepayment_paid
                || $invitation->prepayment_paid_status === BookingHunterInvitation::PREPAYMENT_PAID) {
                throw new ConflictException(
                    errorCode: 'paid_hunter_cannot_be_removed',
                    domain: 'booking',
                );
            }

            $invitation->delete();

            event(new BookingHistoryUpdatedEvent(
                $booking,
                $hunterId,
                BookingHistoryUpdatedEvent::ACTION_REMOVED,
            ));
        });
    }

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

    public function replace(string $code, ReplaceHunterData $data, User $actor): ReplaceHunterResultData
    {
        return DB::transaction(function () use ($code, $data, $actor): ReplaceHunterResultData {
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

            if (!in_array($booking->status, [
                Booking::FINISHED_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
            ], true)) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_replace_not_allowed',
                    domain: 'booking',
                );
            }

            if ($data->oldHunterId === (int) $masterHunter->invited_by) {
                throw new ConflictException(
                    errorCode: 'master_hunter_cannot_be_replaced',
                    domain: 'booking',
                );
            }

            $invitation = BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where('hunter_id', $data->oldHunterId)
                ->lockForUpdate()
                ->first();

            if (!$invitation) {
                throw new NotFoundException(
                    errorCode: 'booking_invitation_not_found',
                    domain: 'booking',
                );
            }

            if ((bool) $invitation->prepayment_paid) {
                throw new ConflictException(
                    errorCode: 'hunter_prepayment_already_paid',
                    domain: 'booking',
                );
            }

            $hunterAlreadyInvited = BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where('hunter_id', $data->hunterId)
                ->exists();

            if ($hunterAlreadyInvited) {
                throw new ConflictException(
                    errorCode: 'hunter_already_in_booking',
                    domain: 'booking',
                );
            }

            $hunter = User::query()->find($data->hunterId);

            if (!$hunter) {
                throw new NotFoundException(
                    errorCode: 'user_not_found',
                    domain: 'booking',
                );
            }

            $invitation->hunter_id = $hunter->id;
            $invitation->email = $hunter->email ?: null;

            if ($booking->status === Booking::PREPAYMENT_COLLECTION) {
                $invitation->prepayment_paid = false;
                $invitation->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_PENDING;
            }

            $invitation->save();

            if ($booking->status === Booking::PREPAYMENT_COLLECTION) {
                BookingHunterInvitation::query()
                    ->where('booking_hunter_id', $masterHunter->id)
                    ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                    ->where('prepayment_paid', false)
                    ->where('prepayment_paid_status', BookingHunterInvitation::PREPAYMENT_UNPAID)
                    ->update([
                        'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PENDING,
                        'updated_at' => now(),
                    ]);

                $this->bookingCollectionService->restartPaidTimer($booking);
            }

            return new ReplaceHunterResultData(
                invitation: $invitation,
                hunter: $hunter,
            );
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
