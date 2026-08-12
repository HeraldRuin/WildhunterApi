<?php

namespace Modules\Booking\Dto;

use App\Models\User;
use Modules\Booking\Models\BookingHunterInvitation;

class ReplaceHunterResultData
{
    public function __construct(
        public BookingHunterInvitation $invitation,
        public User $hunter,
    ) {}
}
