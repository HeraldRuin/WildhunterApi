<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingCollectionService
{
    private const int DEFAULT_TIMER_HOURS = 24;

    public function __construct(
        private readonly BookingMailService $bookingMailService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {
    }

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     *
     */
    public function start(string $code, User $user): array
    {
        $result = DB::transaction(function () use ($code, $user): array {
            $booking = $this->findForUpdate($code);
            $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::CONFIRMED) {
                throw new ConflictException(
                    errorCode: 'booking_collection_not_startable',
                    domain: 'collection',
                );
            }

            return $this->restartCollectionTimer($booking);
        });

        $this->bookingMailService->sendStartCollection($result['booking']);
        $this->bookingNotificationService->sendCollectionStarted($result['booking']);
        BookingUpdatedEvent::dispatchSafely($result['booking']);

        return $result;
    }

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     */
    public function extend(string $code, User $user): array
    {
        $result = DB::transaction(function () use ($code, $user): array {
            $booking = $this->findForUpdate($code);
            $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::START_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_collection_not_extendable',
                    domain: 'collection',
                );
            }

            $endAt = DB::table('bc_booking_meta')
                ->where('booking_id', $booking->id)
                ->where('name', 'collection_end_at')
                ->value('val');

            if (!$endAt) {
                throw new ConflictException(
                    errorCode: 'collection_timer_not_found',
                    domain: 'collection',
                );
            }

            if (Carbon::parse($endAt)->isFuture()) {
                throw new ConflictException(
                    errorCode: 'collection_timer_not_expired',
                    domain: 'collection',
                );
            }

            return $this->restartCollectionTimer($booking);
        });

        BookingUpdatedEvent::dispatchSafely($result['booking']);

        return $result;
    }

    /**
     * @return array{booking: Booking, start_at?: string, end_at?: string, hours?: int}
     *
     */
    public function finish(string $code, User $user): array
    {
        $result = DB::transaction(function () use ($code, $user): array {
            $booking = $this->findForUpdate($code);
            $masterHunter = $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::START_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_gathering_not_started',
                    domain: 'booking',
                );
            }

            if ($booking->type !== Booking::BookingTypeHotel) {
                $requiredHunters = $this->getRequiredHuntersCount($booking);
                $activeInvitationsCount = BookingHunterInvitation::query()
                    ->where('booking_hunter_id', $masterHunter->id)
                    ->whereNotIn('status', [
                        BookingHunterInvitation::STATUS_DECLINED,
                        'removed',
                    ])
                    ->count();

                if ($activeInvitationsCount < $requiredHunters) {
                    throw new ConflictException(
                        errorCode: 'not_enough_hunters',
                        domain: 'booking',
                        context: [
                            'required' => $requiredHunters,
                            'invited' => $activeInvitationsCount,
                        ],
                    );
                }

                $confirmedInvitationsCount = BookingHunterInvitation::query()
                    ->where('booking_hunter_id', $masterHunter->id)
                    ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                    ->count();

                if ($confirmedInvitationsCount < $requiredHunters) {
                    throw new ConflictException(
                        errorCode: 'not_all_hunters_confirmed',
                        domain: 'booking',
                        context: [
                            'required' => $requiredHunters,
                            'confirmed' => $confirmedInvitationsCount,
                        ],
                    );
                }
            }

            if ($booking->type === Booking::BookingTypeAnimal) {
                $booking->status = Booking::FINISHED_COLLECTION;
                $booking->save();

                return ['booking' => $booking];
            }

            $booking->status = Booking::PREPAYMENT_COLLECTION;
            $booking->save();
            $timer = $this->startPaidTimer($booking);

            return [
                'booking' => $booking,
                ...$timer,
            ];
        });

        $this->bookingMailService->sendFinishCollection($result['booking']);

        if ($result['booking']->status === Booking::PREPAYMENT_COLLECTION) {
            $this->bookingNotificationService->sendPrepaymentCollectionStarted($result['booking']);
        } else {
            $this->bookingNotificationService->sendCollectionFinished($result['booking']);
        }

        BookingUpdatedEvent::dispatchSafely($result['booking']);

        return $result;
    }

    public function expirePrepayment(string $code, User $user): void
    {
        DB::transaction(function () use ($code, $user): void {
            $booking = $this->findForUpdate($code);
            $masterHunter = $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::PREPAYMENT_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_prepayment_collection_not_active',
                    domain: 'booking',
                );
            }

            $endAt = DB::table('bc_booking_meta')
                ->where('booking_id', $booking->id)
                ->where('name', 'paid_end_at')
                ->value('val');

            if (!$endAt) {
                throw new ConflictException(
                    errorCode: 'prepayment_timer_not_found',
                    domain: 'booking',
                );
            }

            if (Carbon::parse($endAt)->isFuture()) {
                throw new ConflictException(
                    errorCode: 'prepayment_timer_not_expired',
                    domain: 'booking',
                );
            }

            BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                ->where('prepayment_paid', false)
                ->where('prepayment_paid_status', BookingHunterInvitation::PREPAYMENT_PENDING)
                ->update([
                    'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_UNPAID,
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function cancel(string $code, User $user): Booking
    {
        $invitations = collect();

        $booking = DB::transaction(function () use ($code, $user, &$invitations): Booking {
            $booking = $this->findForUpdate($code);
            $masterHunter = $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::START_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_gathering_not_started',
                    domain: 'booking',
                );
            }

            $invitations = BookingHunterInvitation::query()
                ->with('hunter')
                ->where('booking_hunter_id', $masterHunter->id)
                ->get();

            Booking::query()
                ->whereKey($booking->id)
                ->update(['status' => Booking::CONFIRMED]);

            DB::table('bc_booking_meta')
                ->where('booking_id', $booking->id)
                ->whereIn('name', [
                    'collection_start_at',
                    'collection_timer_hours',
                    'collection_end_at',
                    'paid_start_at',
                    'paid_timer_hours',
                    'paid_end_at',
                    'beds_start_at',
                    'beds_timer_hours',
                    'beds_end_at',
                ])
                ->delete();

            BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterHunter->id)
                ->where(function ($query) use ($masterHunter) {
                    $query->where('hunter_id', '!=', $masterHunter->invited_by)
                        ->orWhereNull('hunter_id');
                })
                ->forceDelete();

            $booking->status = Booking::CONFIRMED;

            return $booking;
        });

        $this->bookingMailService->sendCollectionCancelled($booking, $invitations);
        $this->bookingNotificationService->sendCollectionCancelled($booking, $invitations);
        BookingUpdatedEvent::dispatchSafely($booking);

        return $booking;
    }

    /**
     * @throws NotFoundException
     */
    private function findForUpdate(string $code): Booking
    {
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

        return $booking;
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

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     */
    private function restartCollectionTimer(Booking $booking): array
    {
        $hours = $this->getCollectionTimerHours($booking);
        $now = Carbon::now();
        $startAt = $now->toIso8601String();
        $endAt = $now->copy()->addHours($hours)->toIso8601String();

        Booking::query()
            ->whereKey($booking->id)
            ->update(['status' => Booking::START_COLLECTION]);

        DB::table('bc_booking_meta')
            ->where('booking_id', $booking->id)
            ->whereIn('name', [
                'collection_start_at',
                'collection_timer_hours',
                'collection_end_at',
                'paid_start_at',
                'paid_timer_hours',
                'paid_end_at',
                'beds_start_at',
                'beds_timer_hours',
                'beds_end_at',
            ])
            ->delete();

        DB::table('bc_booking_meta')->insert([
            [
                'booking_id' => $booking->id,
                'name' => 'collection_start_at',
                'val' => $startAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'collection_timer_hours',
                'val' => (string) $hours,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'collection_end_at',
                'val' => $endAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $booking->status = Booking::START_COLLECTION;

        return [
            'booking' => $booking,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'hours' => $hours,
        ];
    }

    private function getCollectionTimerHours(Booking $booking): int
    {
        if (!$booking->hotel_id) {
            return self::DEFAULT_TIMER_HOURS;
        }

        $hours = $booking->hotel?->collection_timer_hours;

        return $hours !== null && $hours > 0
            ? (int) $hours
            : self::DEFAULT_TIMER_HOURS;
    }

    private function getRequiredHuntersCount(Booking $booking): int
    {
        return max(1, $booking->getNeededHuntersCount());
    }

    /**
     * @return array{start_at: string, end_at: string, hours: int}
     */
    public function restartPaidTimer(Booking $booking): array
    {
        return $this->startPaidTimer($booking);
    }

    /**
     * @return array{start_at: string, end_at: string, hours: int}
     */
    public function startBedTimer(Booking $booking): array
    {
        $hours = $booking->hotel?->bed_timer_hours;
        $hours = $hours !== null && $hours > 0 ? (int) $hours : self::DEFAULT_TIMER_HOURS;
        $now = Carbon::now();
        $startAt = $now->toIso8601String();
        $endAt = $now->copy()->addHours($hours)->toIso8601String();

        $booking->status = Booking::BED_COLLECTION;
        $booking->save();

        DB::table('bc_booking_meta')
            ->where('booking_id', $booking->id)
            ->whereIn('name', [
                'collection_start_at',
                'collection_timer_hours',
                'collection_end_at',
                'paid_start_at',
                'paid_timer_hours',
                'paid_end_at',
                'beds_start_at',
                'beds_timer_hours',
                'beds_end_at',
            ])
            ->delete();

        DB::table('bc_booking_meta')->insert([
            [
                'booking_id' => $booking->id,
                'name' => 'beds_start_at',
                'val' => $startAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'beds_timer_hours',
                'val' => (string) $hours,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'beds_end_at',
                'val' => $endAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        BookingUpdatedEvent::dispatchSafely($booking);

        return [
            'start_at' => $startAt,
            'end_at' => $endAt,
            'hours' => $hours,
        ];
    }

    /**
     * @return array{start_at: string, end_at: string, hours: int}
     */
    private function startPaidTimer(Booking $booking): array
    {
        $hours = $this->getPaidTimerHours($booking);
        $now = Carbon::now();
        $startAt = $now->toIso8601String();
        $endAt = $now->copy()->addHours($hours)->toIso8601String();

        DB::table('bc_booking_meta')
            ->where('booking_id', $booking->id)
            ->whereIn('name', [
                'collection_start_at',
                'collection_timer_hours',
                'collection_end_at',
                'paid_start_at',
                'paid_timer_hours',
                'paid_end_at',
                'beds_start_at',
                'beds_timer_hours',
                'beds_end_at',
            ])
            ->delete();

        DB::table('bc_booking_meta')->insert([
            [
                'booking_id' => $booking->id,
                'name' => 'paid_start_at',
                'val' => $startAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'paid_timer_hours',
                'val' => (string) $hours,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'booking_id' => $booking->id,
                'name' => 'paid_end_at',
                'val' => $endAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [
            'start_at' => $startAt,
            'end_at' => $endAt,
            'hours' => $hours,
        ];
    }

    private function getPaidTimerHours(Booking $booking): int
    {
        if (!$booking->hotel_id) {
            return self::DEFAULT_TIMER_HOURS;
        }

        $hours = $booking->hotel?->paid_timer_hours;

        return $hours !== null && $hours > 0
            ? (int) $hours
            : self::DEFAULT_TIMER_HOURS;
    }
}
