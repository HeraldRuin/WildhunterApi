<?php

namespace Modules\Booking\Dto;

use Modules\Booking\Models\Booking;

readonly class BookingHistoryItemData
{
    public function __construct(
        public Booking $booking,
        public string $statusForUser,
        public string $statusLabel,
        public string $displayStatus,
        public bool $isMasterHunter,
        public bool $isInvited,
        public bool $invitationAccepted,
        /** @var array<string, mixed> */
        public array $collection,
        /** @var array<string, mixed> */
        public array $details,
        /** @var array<string, mixed> */
        public array $payment,
        /** @var list<array{code: string, label: string}> */
        public array $availableActions,
    ) {}
}
