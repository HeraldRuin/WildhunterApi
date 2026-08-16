<?php

namespace Modules\Booking\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class NewBookingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        protected string $emailType = 'admin',
        protected ?User $baseAdmin = null,
    ) {
    }

    public function build(): self
    {
        $subject = $this->emailType === 'customer'
            ? __('booking.email.new_booking_customer_subject')
            : __('booking.email.new_booking_admin_subject');

        return $this->subject($subject)
            ->view('Booking::emails.new-booking')
            ->with([
                'booking' => $this->booking,
                'to' => $this->emailType,
                'baseAdmin' => $this->baseAdmin,
            ]);
    }
}
