<?php

namespace Modules\Booking\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;

class BookingCustomerService
{
    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function change(string $code, int $userId, User $actor): array
    {
        if (!is_baseAdmin()) {
            throw new ForbiddenException(
                errorCode: 'booking_access_denied',
                domain: 'booking',
            );
        }

        return DB::transaction(function () use ($code, $userId, $actor): array {
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

            $hotelIds = $actor->hotels()->pluck('id');

            if ($hotelIds->isEmpty() || !$hotelIds->contains($booking->hotel_id)) {
                throw new ForbiddenException(
                    errorCode: 'booking_access_denied',
                    domain: 'booking',
                );
            }

            $customer = User::query()->find($userId);

            if (!$customer) {
                throw new NotFoundException(
                    errorCode: 'user_not_found',
                    domain: 'booking',
                );
            }

            $booking->changeCreator($customer);
            $booking->changeMasterHunterCreator($customer);
            Booking::query()
                ->whereKey($booking->id)
                ->update(['create_user' => $booking->create_user]);
            $booking->masterHunter->save();

            return [
                'code' => 'customer_changed',
            ];
        });
    }
}
