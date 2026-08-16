<?php

namespace Modules\Booking\Services;

use App\Models\User;
use App\Service\MailService;
use Modules\Booking\Emails\HunterMessageEmail;
use Modules\Booking\Emails\NewBookingEmail;
use Modules\Booking\Emails\StatusFinishCollectionEmail;
use Modules\Booking\Emails\StatusStartCollectionEmail;
use Modules\Booking\Emails\StatusUpdatedEmail;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingMailService
{
    public function __construct(
        private readonly MailService $mailService,
    ) {
    }

    public function sendNewBooking(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking): void {
            $baseAdmin = $this->baseAdmin($booking);

            if ($baseAdmin?->email) {
                $this->mailService->send(
                    $baseAdmin->email,
                    new NewBookingEmail($booking, 'admin', $baseAdmin),
                );
            }

            $creator = $this->creator($booking);

            if ($creator?->email) {
                $this->mailService->send(
                    $creator->email,
                    new NewBookingEmail($booking, 'customer'),
                );
            }
        });
    }

    public function sendStatusUpdated(Booking $booking, ?string $customMessage = null): void
    {
        $this->withLocale($booking, function () use ($booking, $customMessage): void {
            $baseAdmin = $this->baseAdmin($booking);
            $baseAdminEmail = $baseAdmin?->email;
            $adminEmail = setting_item('admin_email');

            if ($adminEmail && $adminEmail !== $baseAdminEmail) {
                $this->mailService->send(
                    $adminEmail,
                    new StatusUpdatedEmail($booking, 'admin', $customMessage),
                );
            }

            if ($baseAdminEmail) {
                $this->mailService->send(
                    $baseAdminEmail,
                    new StatusUpdatedEmail($booking, 'admin', $customMessage, $baseAdmin),
                );
            }

            $customerEmail = $this->creator($booking)?->email ?: $booking->email;

            if ($customerEmail) {
                $this->mailService->send(
                    $customerEmail,
                    new StatusUpdatedEmail($booking, 'customer', $customMessage),
                );
            }
        });
    }

    public function sendStartCollection(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking): void {
            $baseAdmin = $this->baseAdmin($booking);

            if (!$baseAdmin?->email) {
                return;
            }

            $this->mailService->send(
                $baseAdmin->email,
                new StatusStartCollectionEmail($booking, 'BaseAdmin', $baseAdmin),
            );
        });
    }

    public function sendFinishCollection(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking): void {
            $baseAdmin = $this->baseAdmin($booking);

            if ($baseAdmin?->email) {
                $this->mailService->send(
                    $baseAdmin->email,
                    new StatusFinishCollectionEmail($booking, 'BaseAdmin', $baseAdmin),
                );
            }

            $masterHunter = $booking->masterHunter()->first();

            if (!$masterHunter) {
                return;
            }

            $invitations = BookingHunterInvitation::query()
                ->with('hunter')
                ->where('booking_hunter_id', $masterHunter->id)
                ->get();

            foreach ($invitations as $invitation) {
                if ((int) $invitation->hunter_id === (int) $masterHunter->invited_by) {
                    continue;
                }

                $email = $invitation->hunter?->email ?: $invitation->email;

                if (!$email) {
                    continue;
                }

                $this->mailService->send(
                    $email,
                    new StatusFinishCollectionEmail($booking, 'customer', $invitation->hunter),
                );
            }
        });
    }

    public function sendHunterInvitation(Booking $booking, User $hunter): void
    {
        if (empty($hunter->email) || (int) $hunter->id === (int) $booking->create_user) {
            return;
        }

        $this->withLocale($booking, function () use ($booking, $hunter): void {
            $this->mailService->send(
                $hunter->email,
                new HunterMessageEmail(
                    $booking,
                    $hunter,
                    __('booking.email.invitation_body', [
                        'name' => $this->displayName($this->creator($booking)),
                    ]),
                    true,
                ),
            );
        });
    }

    /**
     * @param iterable<BookingHunterInvitation> $invitations
     */
    public function sendCollectionCancelled(Booking $booking, iterable $invitations): void
    {
        $this->withLocale($booking, function () use ($booking, $invitations): void {
            foreach ($invitations as $invitation) {
                $hunter = $invitation->hunter;
                $email = $hunter?->email ?: $invitation->email;

                if (!$email) {
                    continue;
                }

                $this->mailService->send(
                    $email,
                    new HunterMessageEmail(
                        $booking,
                        $hunter ?: $this->virtualHunter($email),
                        __('booking.successes.hunter_gathering_cancelled'),
                    ),
                );
            }

            $this->sendStatusUpdated(
                $booking,
                __('booking.successes.hunter_gathering_cancelled'),
            );
        });
    }

    public function sendCancelled(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking): void {
            if (is_baseAdmin()) {
                $creatorEmail = $this->creator($booking)?->email;

                if ($creatorEmail) {
                    $this->mailService->send(
                        $creatorEmail,
                        new StatusUpdatedEmail($booking, 'customer'),
                    );
                }

                return;
            }

            $baseAdmin = $this->baseAdmin($booking);

            if ($baseAdmin?->email) {
                $this->mailService->send(
                    $baseAdmin->email,
                    new StatusUpdatedEmail($booking, 'admin', null, $baseAdmin),
                );
            }

            $customerEmail = $this->creator($booking)?->email ?: $booking->email;

            if ($customerEmail && $customerEmail !== $baseAdmin?->email) {
                $this->mailService->send(
                    $customerEmail,
                    new StatusUpdatedEmail($booking, 'customer'),
                );
            }
        });
    }

    private function baseAdmin(Booking $booking): ?User
    {
        $booking->loadMissing('hotel');

        if (!$booking->hotel?->admin_base) {
            return null;
        }

        return User::query()->find($booking->hotel->admin_base);
    }

    private function creator(Booking $booking): ?User
    {
        $booking->loadMissing('creator');

        return $booking->creator;
    }

    private function displayName(?User $user): string
    {
        if (!$user) {
            return '';
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : (string) ($user->user_name ?: $user->email ?: '');
    }

    private function virtualHunter(string $email): User
    {
        $user = new User();
        $user->id = 0;
        $user->email = $email;

        return $user;
    }

    private function withLocale(Booking $booking, \Closure $callback): void
    {
        $old = app()->getLocale();

        if ($locale = $booking->getMeta('locale')) {
            app()->setLocale($locale);
        }

        $callback();

        app()->setLocale($old);
    }
}
