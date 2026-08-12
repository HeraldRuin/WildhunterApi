<?php

namespace Modules\Booking\Contracts;

use Modules\Booking\Dto\PaykeeperOrderDTO;

interface PaymentGatewayInterface
{
    /**
     * @return array{external_id: string, payment_url: string, payload: array<string, mixed>}
     */
    public function createInvoice(PaykeeperOrderDTO $order): array;

    public function revokeInvoice(string $externalId): bool;

    /**
     * @return array{status: string, payload: array<string, mixed>}
     */
    public function getInvoiceStatus(string $externalId): array;
}
