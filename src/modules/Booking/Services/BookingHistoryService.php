<?php

namespace Modules\Booking\Services;

use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Dto\BookingHistoryData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Role\Models\Role;

class BookingHistoryService
{
    private const array TIMER_META_KEYS = [
        'collection_end_at',
        'paid_end_at',
        'beds_end_at',
    ];

    public function __construct(
        private BookingCalculatingService $bookingCalculatingService,
        private BookingStatusService $bookingStatusService,
        private BookingHistoryItemPresenter $bookingHistoryItemPresenter,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getHistory(User $user, BookingHistoryData $data): array
    {
        $this->handleInvitationCode($data->code, $user);

        if (is_baseAdmin()) {
            $hotel = $user->hotels()->with('location')->first();
            $bookings = Booking::getBookingHistoryForAdminBase($user, $data->status, $data->bookingId);
            $bookings = $this->attachCalculations($bookings, $user);
            $bookings = $this->attachPresentation($bookings, $user, Role::ADMIN);

            return [
                'role' => Role::ADMIN,
                'hotel' => $hotel ? [
                    'id' => $hotel->id,
                    'title' => $hotel->title,
                    'slug' => $hotel->slug,
                    'location' => $hotel->location ? [
                        'slug' => $hotel->location->slug,
                    ] : null,
                ] : null,
                'statuses' => $this->bookingStatusService->getAllowedStatuses(Role::ADMIN),
                'dropdown_statuses' => $this->bookingStatusService->getDropdownStatuses(),
                'bookings' => $bookings,
            ];
        }

        $bookings = Booking::getBookingHistory($data->status, $user->id, false, false, false, $data->bookingId);
        $bookings = $this->attachCalculations($bookings, $user);
        $bookings = $this->attachPresentation($bookings, $user, Role::CUSTOMER);

        return [
            'role' => Role::CUSTOMER,
            'hotel' => null,
            'statuses' => $this->bookingStatusService->getAllowedStatuses(Role::CUSTOMER),
            'dropdown_statuses' => [],
            'bookings' => $bookings,
        ];
    }

    /**
     * @throws NotFoundException
     */
    private function handleInvitationCode(?string $code, User $user): void
    {
        if (!$code) {
            return;
        }

        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $masterHunter = $booking->masterHunter()->first();

        if (!$masterHunter) {
            return;
        }

        BookingHunterInvitation::query()->updateOrCreate(
            [
                'booking_hunter_id' => $masterHunter->id,
                'hunter_id' => $user->id,
            ],
            [
                'email' => $user->email,
                'invited' => true,
            ],
        );
    }

    private function attachCalculations(LengthAwarePaginator $bookings, User $user): LengthAwarePaginator
    {
        $bookings->getCollection()->transform(function ($booking) use ($user) {
            $booking->calculation = $this->bookingCalculatingService->calculate($booking, $user);

            return $booking;
        });

        return $bookings;
    }

    private function attachPresentation(
        LengthAwarePaginator $bookings,
        User $user,
        string $role,
    ): LengthAwarePaginator {
        $collection = $bookings->getCollection();

        if ($collection->isEmpty()) {
            return $bookings;
        }

        $collection->load([
            'hunterInvitations.hunter',
            'hunterInvitations.bookingHunter',
        ]);

        $timerMetaByBooking = $this->loadTimerMeta($collection->pluck('id')->all());

        $bookings->setCollection(
            $collection->map(function (Booking $booking) use ($user, $role, $timerMetaByBooking) {
                return $this->bookingHistoryItemPresenter->present(
                    $booking,
                    $user,
                    $role,
                    $timerMetaByBooking[$booking->id] ?? [],
                );
            }),
        );

        return $bookings;
    }

    /**
     * @param list<int> $bookingIds
     * @return array<int, array<string, string>>
     */
    private function loadTimerMeta(array $bookingIds): array
    {
        if ($bookingIds === []) {
            return [];
        }

        $rows = DB::table('bc_booking_meta')
            ->whereIn('booking_id', $bookingIds)
            ->whereIn('name', self::TIMER_META_KEYS)
            ->get(['booking_id', 'name', 'val']);

        $metaByBooking = [];

        foreach ($rows as $row) {
            $metaByBooking[(int) $row->booking_id][$row->name] = $row->val;
        }

        return $metaByBooking;
    }
}
