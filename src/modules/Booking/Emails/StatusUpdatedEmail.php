<?php

namespace Modules\Booking\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class StatusUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public bool $isStatusUpdate = true;

    public string $recipientName;

    public function __construct(
        public Booking $booking,
        public string $emailType = 'admin',
        public ?string $customMessage = null,
        public ?User $baseAdmin = null,
    ) {
        $recipient = $this->emailType === 'customer'
            ? $this->booking->creator
            : $this->baseAdmin;

        $this->recipientName = $this->resolveRecipientName(
            $recipient,
            $this->emailType === 'customer'
                ? __('booking.email.hunter')
                : __('booking.email.base_admin'),
        );
    }

    public function build(): self
    {
        $subject = $this->emailType === 'customer'
            ? __('booking.email.status_updated_customer_subject')
            : __('booking.email.status_updated_admin_subject', [
                'site_name' => setting_item('site_title'),
            ]);

        $view = $this->emailType === 'customer'
            ? 'Booking::emails.new-booking'
            : 'Booking::emails.status-updated-booking';

        return $this->subject($subject)->view($view);
    }

    private function resolveRecipientName(?User $user, string $fallback): string
    {
        if (!$user) {
            return $fallback;
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : (string) ($user->user_name ?: $user->email ?: $fallback);
    }
}
