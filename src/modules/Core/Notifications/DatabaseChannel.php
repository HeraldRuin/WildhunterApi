<?php

namespace Modules\Core\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Core\Models\NotificationPush;

class DatabaseChannel
{
    public function send(object $notifiable, Notification $notification): NotificationPush
    {
        $data = $notification->toDatabase($notifiable);

        return NotificationPush::query()->create([
            'id' => $notification->id,
            'for_admin' => (bool) ($data['for_admin'] ?? false),
            'notifiable_id' => $notifiable->getKey(),
            'notifiable_type' => $notifiable::class,
            'type' => $notification::class,
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
