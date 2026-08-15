<?php

namespace Modules\Booking\Services\Calculation;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\User;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\BookingHistoryActionService;
use Modules\Booking\Services\Calculation\Strategies\BookingCalculationStrategyResolver;
use Modules\Role\Models\Role;

readonly class BookingCalculatingService
{
    public function __construct(
        private BookingDataBuilder $builder,
        private BookingCalculationStrategyResolver $resolver,
        private BookingHistoryActionService $bookingHistoryActionService,
    ) {}

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws ConflictException
     * @throws ValidationException
     */
    public function getByCode(string $code, User $user): array
    {
        $booking = Booking::query()->where('code', $code)->first();

        if (!$booking) {
            throw new NotFoundException(
                errorCode: 'booking_not_found',
                domain: 'booking',
            );
        }

        $this->ensureCanAccess($booking, $user);
        $this->ensureCalculatingAvailable($booking, $user);

        $result = $this->calculate($booking, $user);

        if (($result['success'] ?? false) === false) {
            throw new ConflictException(
                errorCode: $result['message'] ?? 'calculating_not_available',
                domain: 'calculate',
            );
        }

        return $result;
    }

    /**
     * @throws ValidationException
     */
    public function calculate(Booking $booking, $user): array
    {
        $data = $this->builder->build($booking);

        $strategy = $this->resolver->resolve($booking);

        return $strategy->calculate($booking, $data, $user);
    }

    /**
     * @throws ForbiddenException
     */
    private function ensureCanAccess(Booking $booking, User $user): void
    {
        if ($user->hasRole(Role::ADMIN)) {
            $hotelIds = $user->hotels()->pluck('id');

            if ($hotelIds->isEmpty() || !$hotelIds->contains($booking->hotel_id)) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }

            return;
        }

        $invitation = $booking->getCurrentUserInvitation();

        if (!$invitation || $invitation->status !== BookingHunterInvitation::STATUS_ACCEPTED) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }
    }

    /**
     * @throws ConflictException
     */
    private function ensureCalculatingAvailable(Booking $booking, User $user): void
    {
        $role = $user->hasRole(Role::ADMIN) ? Role::ADMIN : Role::CUSTOMER;
        $hasCalculating = collect($this->bookingHistoryActionService->getAvailableActions($booking, $role))
            ->contains(static fn (array $action) => $action['code'] === 'calculating');

        if (!$hasCalculating) {
            throw new ConflictException(
                errorCode: 'calculating_not_available',
                domain: 'calculate',
            );
        }
    }
}
