<?php

namespace Modules\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\NotificationPush;

class UserNotificationCreatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private readonly string $notificationId;

    private readonly int $userId;

    private readonly string $title;

    private readonly string $message;

    private readonly ?string $link;

    private readonly ?string $category;

    private readonly string $createdAt;

    private readonly string $timeAgo;

    public function __construct(NotificationPush $notification)
    {
        $payload = $notification->payload();

        $this->notificationId = $notification->id;
        $this->userId = (int) $notification->notifiable_id;
        $this->title = (string) ($payload['title'] ?? '');
        $this->message = (string) ($payload['message'] ?? '');
        $this->link = isset($payload['link']) ? (string) $payload['link'] : null;
        $this->category = isset($payload['category']) ? (string) $payload['category'] : null;
        $this->createdAt = $notification->created_at?->toIso8601String() ?? now()->toIso8601String();
        $this->timeAgo = $notification->created_at
            ?->locale(app()->getLocale())
            ->diffForHumans(short: true) ?? '';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("notifications.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificationId,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'category' => $this->category,
            'unread' => true,
            'created_at' => $this->createdAt,
            'time_ago' => $this->timeAgo,
        ];
    }

    public static function dispatchSafely(NotificationPush $notification): void
    {
        try {
            event(new self($notification));
        } catch (\Throwable $exception) {
            Log::warning('Notification broadcast failed', [
                'notification_id' => $notification->id,
                'user_id' => $notification->notifiable_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
