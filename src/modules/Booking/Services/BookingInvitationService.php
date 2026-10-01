<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Dto\ReplaceHunterData;
use Modules\Booking\Dto\ReplaceHunterResultData;
use Modules\Booking\Events\BookingGatheringCompletedEvent;
use Modules\Booking\Events\BookingHistoryUpdatedEvent;
use Modules\Booking\Events\BookingInvitationUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationService
{
    public function __construct(
        private readonly BookingCollectionService $bookingCollectionService,
        private readonly BookingMailService $bookingMailService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {
    }

    public function remove(string $code, int $hunterId, User $actor): void
    {
        $booking = DB::transaction(function () use ($code, $hunterId, $actor): Booking {
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

            return $booking;
        });

        BookingHistoryUpdatedEvent::dispatchSafely(
            $booking,
            $hunterId,
            BookingHistoryUpdatedEvent::ACTION_REMOVED,
        );
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function invite(string $code, int $hunterId, User $actor): BookingHunterInvitation
    {
        [$invitation, $booking, $hunter] = DB::transaction(function () use ($code, $hunterId, $actor): array {
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

            $invitation = BookingHunterInvitation::withTrashed()->firstOrNew([
                'booking_hunter_id' => $masterHunter->id,
                'hunter_id' => $hunter->id,
            ]);

            if ($invitation->trashed()) {
                $invitation->restore();
            }

            $invitation->fill([
                'email' => $hunter->email,
                'invited' => true,
                'status' => BookingHunterInvitation::STATUS_PENDING,
                'invited_at' => now(),
                'accepted_at' => null,
                'declined_at' => null,
                'invitation_token' => "{$booking->code}-{$hunter->id}",
            ]);
            $invitation->save();

            return [$invitation, $booking, $hunter];
        });

        $this->bookingMailService->sendHunterInvitation($booking, $hunter);
        $this->bookingNotificationService->sendHunterInvited($booking, $hunter);
        BookingHistoryUpdatedEvent::dispatchSafely(
            $booking,
            $hunter->id,
            BookingHistoryUpdatedEvent::ACTION_ADDED,
        );

        return $invitation;
    }

    public function replace(string $code, ReplaceHunterData $data, User $actor): ReplaceHunterResultData
    {
        [$result, $booking] = DB::transaction(function () use ($code, $data, $actor): array {
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

            return [
                new ReplaceHunterResultData(
                    invitation: $invitation,
                    hunter: $hunter,
                ),
                $booking,
            ];
        });

        $this->bookingMailService->sendHunterInvitation($booking, $result->hunter);
        $this->bookingNotificationService->sendHunterInvited($booking, $result->hunter);

        return $result;
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function accept(string $code, User $user): BookingHunterInvitation
    {
        [$booking, $invitation, $gatheringFinished, $droppedInvitations] = DB::transaction(function () use ($code, $user): array {
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

            $invitation = BookingHunterInvitation::query()
                ->whereHas('bookingHunter', function ($query) use ($booking) {
                    $query->where('booking_id', $booking->id);
                })
                ->where('hunter_id', $user->id)
                ->whereNotIn('status', [
                    BookingHunterInvitation::STATUS_DECLINED,
                    'removed',
                ])
                ->lockForUpdate()
                ->first();

            if (!$invitation) {
                $this->throwMissingInvitation($booking);
            }

            $alreadyAccepted = $invitation->status === BookingHunterInvitation::STATUS_ACCEPTED;
            $groupSize = (int) ($booking->total_hunting ?? 0);

            if (!$alreadyAccepted && $groupSize > 0 && $booking->countAcceptedHunters() >= $groupSize) {
                throw new ConflictException(
                    errorCode: 'gathering_is_full',
                    domain: 'booking',
                );
            }

            $invitation->status = BookingHunterInvitation::STATUS_ACCEPTED;
            $invitation->accepted_at = now();
            $invitation->declined_at = null;
            $invitation->save();

            $gatheringFinished = $booking->status === Booking::START_COLLECTION
                && $groupSize > 0
                && $booking->countAcceptedHunters() >= $groupSize;

            $droppedInvitations = new Collection();

            if ($gatheringFinished) {
                $this->bookingCollectionService->finishGathering($booking);
                $droppedInvitations = $this->dropUnconfirmedInvitations($booking);
            }

            return [$booking, $invitation, $gatheringFinished, $droppedInvitations];
        });

        $this->bookingNotificationService->sendInvitationAccepted($booking, $user);
        BookingInvitationUpdatedEvent::dispatchSafely(
            $booking,
            $invitation,
            BookingInvitationUpdatedEvent::ACTION_ACCEPTED,
        );

        if ($gatheringFinished) {
            $this->bookingCollectionService->notifyGatheringFinished($booking);
            $this->notifyDroppedHunters($booking, $droppedInvitations);
            $this->broadcastGatheringCompleted($booking, $droppedInvitations);
        }

        return $invitation;
    }

    /**
     * @throws NotFoundException
     */
    public function decline(string $code, User $user): BookingHunterInvitation
    {
        [$booking, $invitation] = $this->findInvitation($code, $user);
        $invitation->status = BookingHunterInvitation::STATUS_DECLINED;
        $invitation->declined_at = now();
        $invitation->save();

        $this->bookingNotificationService->sendInvitationDeclined($booking, $user);
        BookingInvitationUpdatedEvent::dispatchSafely(
            $booking,
            $invitation,
            BookingInvitationUpdatedEvent::ACTION_DECLINED,
        );

        return $invitation;
    }

    /**
     * Неподтверждённые приглашения снимаются со сбора, когда последнее место уже занято.
     *
     * @return Collection<int, BookingHunterInvitation>
     */
    private function dropUnconfirmedInvitations(Booking $booking): Collection
    {
        $invitations = BookingHunterInvitation::query()
            ->with('hunter')
            ->whereHas('bookingHunter', function ($query) use ($booking) {
                $query->where('booking_id', $booking->id);
            })
            ->where(function ($query) {
                $query->where('status', BookingHunterInvitation::STATUS_PENDING)
                    ->orWhereNull('status');
            })
            ->lockForUpdate()
            ->get();

        foreach ($invitations as $invitation) {
            $invitation->forceDelete();
        }

        return $invitations;
    }

    /**
     * @param  Collection<int, BookingHunterInvitation>  $invitations
     */
    private function notifyDroppedHunters(Booking $booking, Collection $invitations): void
    {
        if ($invitations->isEmpty()) {
            return;
        }

        $this->bookingNotificationService->sendGatheringFilled($booking, $invitations);
        $this->bookingMailService->sendGatheringClosed($booking, $invitations);

        foreach ($invitations as $invitation) {
            if (!$invitation->hunter_id) {
                continue;
            }

            BookingHistoryUpdatedEvent::dispatchSafely(
                $booking,
                (int) $invitation->hunter_id,
                BookingHistoryUpdatedEvent::ACTION_REMOVED,
            );
        }
    }

    /**
     * @param  Collection<int, BookingHunterInvitation>  $invitations
     */
    private function broadcastGatheringCompleted(Booking $booking, Collection $invitations): void
    {
        $removedHunterIds = [];
        $removedInvitationIds = [];

        foreach ($invitations as $invitation) {
            $removedInvitationIds[] = (int) $invitation->id;

            if ($invitation->hunter_id) {
                $removedHunterIds[] = (int) $invitation->hunter_id;
            }
        }

        BookingGatheringCompletedEvent::dispatchSafely(
            $booking,
            $removedHunterIds,
            $removedInvitationIds,
        );
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    private function throwMissingInvitation(Booking $booking): never
    {
        if (in_array($booking->status, [
            Booking::FINISHED_COLLECTION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
            Booking::PARTIAL_PAYMENT,
            Booking::COMPLETED,
        ], true)) {
            throw new ConflictException(
                errorCode: 'gathering_is_full',
                domain: 'booking',
            );
        }

        throw new NotFoundException(
            errorCode: 'booking_invitation_not_found',
            domain: 'booking',
        );
    }

    /**
     * @return array{0: Booking, 1: BookingHunterInvitation}
     *
     * @throws NotFoundException
     */
    private function findInvitation(string $code, User $user): array
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

        return [$booking, $invitation];
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
