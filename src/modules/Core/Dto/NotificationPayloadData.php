<?php

namespace Modules\Core\Dto;

final readonly class NotificationPayloadData
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $link = null,
        public ?string $category = null,
        public ?string $entityType = null,
        public ?int $entityId = null,
        public ?string $event = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'category' => $this->category,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'event' => $this->event,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
