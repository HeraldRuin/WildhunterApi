<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\Core\Dto\NotificationPayloadData;

class UserNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly NotificationPayloadData $payload,
        private readonly bool $forAdmin = false,
    ) {
        $this->id = Str::uuid()->toString();
    }

    public function via(object $notifiable): array
    {
        return [DatabaseChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'for_admin' => $this->forAdmin,
            'notification' => $this->payload->toArray(),
        ];
    }
}
