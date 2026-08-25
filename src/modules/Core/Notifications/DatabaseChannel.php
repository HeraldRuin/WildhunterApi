<?php

namespace Modules\Core\Notifications;

use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    public function send(object $notifiable, Notification $notification): mixed
    {
        $data = $notification->toDatabase($notifiable);

        return $notifiable->routeNotificationFor('database')->create([
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
