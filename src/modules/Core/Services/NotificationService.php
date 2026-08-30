<?php

namespace Modules\Core\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Dto\NotificationPayloadData;
use Modules\Core\Events\UserNotificationCreatedEvent;
use Modules\Core\Models\NotificationPush;
use Modules\Core\Notifications\UserNotification;

class NotificationService
{
    public function sendToUser(User|int $user, NotificationPayloadData $payload, bool $forAdmin = false): NotificationPush
    {
        $recipient = $user instanceof User ? $user : User::query()->findOrFail($user);

        $userNotification = new UserNotification($payload, $forAdmin);
        $recipient->notify($userNotification);

        /** @var NotificationPush $notification */
        $notification = NotificationPush::query()->findOrFail($userNotification->id);

        UserNotificationCreatedEvent::dispatchSafely($notification);

        return $notification;
    }

    public function listForUser(User $user, ?string $type = null, int $perPage = 20, bool $forAdmin = false): LengthAwarePaginator
    {
        return $this->baseQuery($user, $forAdmin)
            ->when($type === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($type === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function unreadCount(User $user, bool $forAdmin = false): int
    {
        return $this->baseQuery($user, $forAdmin)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(string $notificationId, User $user, bool $forAdmin = false): bool
    {
        return (bool) $this->baseQuery($user, $forAdmin)
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user, bool $forAdmin = false): int
    {
        return $this->baseQuery($user, $forAdmin)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function baseQuery(User $user, bool $forAdmin = false): Builder
    {
        return NotificationPush::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('for_admin', $forAdmin);
    }
}
