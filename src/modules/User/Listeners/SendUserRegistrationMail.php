<?php

namespace Modules\User\Listeners;

use Illuminate\Auth\Events\Registered;

class SendUserRegistrationMail
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
    }
}
