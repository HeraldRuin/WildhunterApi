<?php

namespace Modules\Booking\Services;

use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Booking\Dto\BookingHistoryData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Role\Models\Role;

class BookingHistoryService
{
    public function __construct(
        private BookingCalculatingService $bookingCalculatingService,
        private BookingStatusService $bookingStatusService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getHistory(User $user, BookingHistoryData $data): array
    {
        $this->handleInvitationCode($data->code, $user);

        if (is_baseAdmin()) {
            $hotel = $user->hotels()->first();
            $bookings = Booking::getBookingHistoryForAdminBase($hotel?->id, $data->status, $data->bookingId);
            $bookings = $this->attachCalculations($bookings, $user);

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
}
