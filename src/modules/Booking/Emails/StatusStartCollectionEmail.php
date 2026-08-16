<?php

namespace Modules\Booking\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class StatusStartCollectionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $emailType = 'BaseAdmin',
        protected ?User $user = null,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('booking.email.status_updated_admin_subject', [
            'site_name' => setting_item('site_title'),
        ]))
            ->view('Booking::emails.start-collection')
            ->with([
                'booking' => $this->booking,
                'to' => $this->emailType,
                'user' => $this->user,
            ]);
    }
}
