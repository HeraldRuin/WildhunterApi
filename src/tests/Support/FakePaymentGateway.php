<?php

namespace Tests\Support;

use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Dto\PaykeeperOrderDTO;
use Throwable;

class FakePaymentGateway implements PaymentGatewayInterface
{
    /** @var list<PaykeeperOrderDTO> */
    public array $invoices = [];

    /** @var list<string> */
    public array $revoked = [];

    /** @var list<string> */
    public array $statusChecks = [];

    public ?Throwable $revokeException = null;

    public ?Throwable $statusException = null;

    /** @var array{status: string, payload: array<string, mixed>}|null */
    public ?array $statusResult = null;

    private int $sequence = 0;

    public function createInvoice(PaykeeperOrderDTO $order): array
    {
        $this->invoices[] = $order;
        $this->sequence++;
        $externalId = 'inv-'.$this->sequence;

        return [
            'external_id' => $externalId,
            'payment_url' => 'https://pay.example/'.$externalId,
            'payload' => ['id' => $externalId],
        ];
    }

    public function revokeInvoice(string $externalId): bool
    {
        if ($this->revokeException) {
            throw $this->revokeException;
        }

        $this->revoked[] = $externalId;

        return true;
    }

    public function getInvoiceStatus(string $externalId): array
    {
        $this->statusChecks[] = $externalId;

        if ($this->statusException) {
            throw $this->statusException;
        }

        return $this->statusResult ?? [
            'status' => 'processing',
            'payload' => ['id' => $externalId],
        ];
    }
}
