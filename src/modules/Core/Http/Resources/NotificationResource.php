<?php

namespace Modules\Core\Http\Resources;

use App\Http\Resources\BaseJsonResource;
use Modules\Core\Models\NotificationPush;

class NotificationResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        /** @var NotificationPush $notification */
        $notification = $this->resource;
        $payload = $notification->payload();

        return [
            'id' => $notification->id,
            'title' => (string) ($payload['title'] ?? ''),
            'message' => (string) ($payload['message'] ?? ''),
            'link' => $payload['link'] ?? null,
            'category' => $payload['category'] ?? null,
            'entity_type' => $payload['entity_type'] ?? null,
            'entity_id' => isset($payload['entity_id']) ? (int) $payload['entity_id'] : null,
            'event' => $payload['event'] ?? null,
            'unread' => $notification->read_at === null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'time_ago' => $notification->created_at
                ?->locale(app()->getLocale())
                ->diffForHumans(short: true),
        ];
    }
}
