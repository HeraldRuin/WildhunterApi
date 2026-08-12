<?php

namespace Modules\Booking\Dto;

final readonly class PaykeeperOrderDTO
{
    public function __construct(
        public string $orderId,
        public float $amount,
        public string $customerName,
        public ?string $email = null,
        public ?string $phone = null,
        public string $description = '',
        public string $currency = 'RUB',
        public ?string $expiresAt = null,
    ) {
    }
}
