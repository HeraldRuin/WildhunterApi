<?php

namespace Modules\User\Listeners;

use Illuminate\Auth\Events\Registered;
use Modules\User\Services\UserMailService;

class SendUserRegistrationMail
{
    public function __construct(
        private readonly UserMailService $userMailService,
    ) {
    }

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (!$user) {
            return;
        }

        $this->userMailService->sendRegistered($user);
    }
}
