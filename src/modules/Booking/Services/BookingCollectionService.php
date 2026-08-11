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

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     *
     */
    public function start(string $code, User $user): array
    {
        return DB::transaction(function () use ($code, $user): array {
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
    }

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     */
    public function extend(string $code, User $user): array
    {
        return DB::transaction(function () use ($code, $user): array {
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
    }

    /**
     * @return array{booking: Booking, start_at?: string, end_at?: string, hours?: int}
     *
     */
    public function finish(string $code, User $user): array
    {
        return DB::transaction(function () use ($code, $user): array {
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
                event(new BookingUpdatedEvent($booking));

                return ['booking' => $booking];
            }

            $booking->status = Booking::PREPAYMENT_COLLECTION;
            $booking->save();
            $timer = $this->startPaidTimer($booking);
            event(new BookingUpdatedEvent($booking));

            return [
                'booking' => $booking,
                ...$timer,
            ];
        });
    }

    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function cancel(string $code, User $user): Booking
    {
        return DB::transaction(function () use ($code, $user): Booking {
            $booking = $this->findForUpdate($code);
            $masterHunter = $this->ensureMasterHunter($booking, $user);

            if ($booking->status !== Booking::START_COLLECTION) {
                throw new ConflictException(
                    errorCode: 'booking_hunter_gathering_not_started',
                    domain: 'booking',
                );
            }

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
                ->delete();

            $booking->status = Booking::CONFIRMED;
            event(new BookingUpdatedEvent($booking));

            return $booking;
        });
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
        if ($booking->animal_id && $booking->hotel_id) {
            $huntersCount = DB::table('bc_hotel_animals')
                ->where('hotel_id', $booking->hotel_id)
                ->where('animal_id', $booking->animal_id)
                ->value('hunters_count');

            return $huntersCount !== null && (int) $huntersCount > 0
                ? (int) $huntersCount
                : 1;
        }

        $huntersCount = match ($booking->type) {
            Booking::BookingTypeHotel => (int) ($booking->total_guests ?? 0),
            Booking::BookingTypeAnimal,
            Booking::BookingTypeHotelAnimal => (int) ($booking->total_hunting ?? 0),
            default => 0,
        };

        return max(1, $huntersCount);
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
