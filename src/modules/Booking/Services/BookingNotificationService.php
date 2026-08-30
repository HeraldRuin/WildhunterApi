<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Core\Dto\NotificationPayloadData;
use Modules\Core\Services\NotificationService;

class BookingNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function sendNewBooking(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);
        $link = $this->bookingLink($booking);

        $baseAdmin = $this->baseAdmin($booking);

        if ($baseAdmin) {
            $this->sendSafely(
                $baseAdmin,
                new NotificationPayloadData(
                    title: __('booking.notifications.new_booking_admin_title'),
                    message: __('booking.notifications.new_booking_admin_message', [
                        'number' => $number,
                    ]),
                    link: $link,
                    category: 'booking',
                    entityType: 'booking',
                    entityId: (int) $booking->id,
                    event: 'booking.created',
                ),
                forAdmin: true,
            );
        }

        $this->sendToCreator(
            $booking,
            title: __('booking.notifications.new_booking_customer_title'),
            message: __('booking.notifications.new_booking_customer_message', [
                'number' => $number,
            ]),
            event: 'booking.created',
        );
    }

    public function sendBookingConfirmed(Booking $booking): void
    {
        $number = $this->bookingNumber($booking);

        $this->sendToCreator(
            $booking,
            title: __('booking.notifications.booking_confirmed_title'),
            message: __('booking.notifications.booking_confirmed_message', [
                'number' => $number,
            ]),
            event: 'booking.confirmed',
        );
    }

    private function baseAdmin(Booking $booking): ?User
    {
        $booking->loadMissing('hotel');

        if (!$booking->hotel?->admin_base) {
            return null;
        }

        return User::query()->find($booking->hotel->admin_base);
    }

    private function sendToCreator(
        Booking $booking,
        string $title,
        string $message,
        string $event,
    ): void {
        $creator = $this->creator($booking);

        if (!$creator) {
            return;
        }

        $this->sendSafely(
            $creator,
            new NotificationPayloadData(
                title: $title,
                message: $message,
                link: $this->bookingLink($booking),
                category: 'booking',
                entityType: 'booking',
                entityId: (int) $booking->id,
                event: $event,
            ),
            forAdmin: false,
        );
    }

    private function creator(Booking $booking): ?User
    {
        $booking->loadMissing('creator');

        return $booking->creator;
    }

    private function bookingNumber(Booking $booking): string
    {
        return (string) ($booking->booking_number ?: $booking->code);
    }

    private function bookingLink(Booking $booking): string
    {
        return '/profile/bookings?booking_id=' . (int) $booking->id;
    }

    private function sendSafely(User $user, NotificationPayloadData $payload, bool $forAdmin): void
    {
        try {
            $this->notificationService->sendToUser($user, $payload, $forAdmin);
        } catch (\Throwable $exception) {
            Log::warning('Notification send failed', [
                'user_id' => $user->id,
                'for_admin' => $forAdmin,
                'event' => $payload->event,
                'entity_id' => $payload->entityId,
                'trace_id' => request()->attributes->get('trace_id'),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
