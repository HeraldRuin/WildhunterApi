<?php

namespace Modules\Booking\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class HunterMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public User $hunter,
        public string $bodyText,
        public bool $isInvitation = false,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('booking.email.hunter_message_subject', [
            'id' => $this->booking->id,
        ]))
            ->view('Booking::emails.hunter-message')
            ->with([
                'booking' => $this->booking,
                'hunter' => $this->hunter,
                'bodyText' => $this->bodyText,
                'isInvitation' => $this->isInvitation,
            ]);
    }
}
