<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;

class BookingStartCollectionService
{
    private const int DEFAULT_TIMER_HOURS = 24;

    /**
     * @return array{booking: Booking, start_at: string, end_at: string, hours: int}
     *
     */
    public function start(string $code, User $user): array
    {
        return DB::transaction(function () use ($code, $user): array {
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

            $isMasterHunter = $booking->masterHunter()
                ->where('invited_by', $user->id)
                ->exists();

            if (!$isMasterHunter) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }

            if ($booking->status !== Booking::CONFIRMED) {
                throw new ConflictException(
                    errorCode: 'booking_collection_not_startable',
                    domain: 'collection',
                );
            }

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
        });
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
}
