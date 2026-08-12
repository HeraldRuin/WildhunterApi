<?php

namespace Modules\Booking\Gateways;

use App\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Dto\PaykeeperOrderDTO;
use Throwable;

class PaykeeperGateway implements PaymentGatewayInterface
{
    public function createInvoice(PaykeeperOrderDTO $order): array
    {
        $response = $this->send('post', (string) config('paykeeper.invoice_path'), [
            'token' => $this->token(),
            'pay_amount' => number_format($order->amount, 2, '.', ''),
            'clientid' => $order->customerName,
            'orderid' => $order->orderId,
            'client_email' => $order->email,
            'client_phone' => $order->phone,
            'service_name' => $order->description,
            'expiry' => $order->expiresAt,
        ]);

        $payload = $this->providerPayload($response, 'create_invoice');
        $externalId = (string) ($payload['invoice_id'] ?? $payload['id'] ?? '');
        $paymentUrl = (string) ($payload['invoice_url'] ?? '');

        if ($externalId === '' || $paymentUrl === '') {
            throw new PaymentGatewayException(
                errorCode: 'payment_gateway_invalid_response',
                context: ['operation' => 'create_invoice'],
            );
        }

        return [
            'external_id' => $externalId,
            'payment_url' => $paymentUrl,
            'payload' => $payload,
        ];
    }

    public function revokeInvoice(string $externalId): bool
    {
        $payload = $this->providerPayload($this->send(
            'post',
            (string) config('paykeeper.revoke_path'),
            ['id' => $externalId, 'token' => $this->token()],
        ), 'revoke_invoice');

        if (($payload['result'] ?? null) !== 'success') {
            throw new PaymentGatewayException(
                errorCode: 'payment_gateway_invalid_response',
                context: ['operation' => 'revoke_invoice'],
            );
        }

        return true;
    }

    public function getInvoiceStatus(string $externalId): array
    {
        $response = $this->send('get', (string) config('paykeeper.status_path'), [
            'id' => $externalId,
        ]);
        $payload = $this->providerPayload($response, 'invoice_status');
        $rawStatus = strtolower((string) ($payload['status'] ?? $payload[0]['status'] ?? ''));

        return [
            'status' => match ($rawStatus) {
                'paid', 'success', 'completed' => 'paid',
                'expired' => 'expired',
                'failed', 'canceled', 'cancelled' => 'failed',
                default => 'processing',
            },
            'payload' => $payload,
        ];
    }

    private function token(): string
    {
        return Cache::remember(
            (string) config('paykeeper.cache_key', 'paykeeper:token'),
            now()->addMinutes((int) config('paykeeper.token_ttl_minutes', 10)),
            function (): string {
                $payload = $this->providerPayload(
                    $this->send('get', (string) config('paykeeper.token_path')),
                    'access_token',
                );
                $token = (string) ($payload['token'] ?? '');

                if ($token === '') {
                    throw new PaymentGatewayException(errorCode: 'payment_gateway_token_error');
                }

                return $token;
            },
        );
    }

    private function send(string $method, string $path, array $data = []): Response
    {
        try {
            $response = $this->client()->{$method}($this->url($path), $data);
        } catch (Throwable $exception) {
            throw new PaymentGatewayException(
                message: $exception->getMessage(),
                context: ['operation' => $path],
            );
        }

        if (!$response->successful()) {
            throw new PaymentGatewayException(
                context: ['operation' => $path, 'provider_status' => $response->status()],
            );
        }

        return $response;
    }

    private function client(): PendingRequest
    {
        $baseUrl = (string) config('paykeeper.base_url');
        $clientId = (string) config('paykeeper.client_id');
        $clientSecret = (string) config('paykeeper.client_secret');

        if ($baseUrl === '' || $clientId === '' || $clientSecret === '') {
            throw new PaymentGatewayException(errorCode: 'payment_gateway_not_configured');
        }

        return Http::asForm()
            ->acceptJson()
            ->withBasicAuth($clientId, $clientSecret)
            ->connectTimeout((int) config('paykeeper.connect_timeout'))
            ->timeout((int) config('paykeeper.timeout'))
            ->retry(2, 200, throw: false);
    }

    private function json(Response $response): array
    {
        $payload = $response->json();

        if (!is_array($payload)) {
            throw new PaymentGatewayException(errorCode: 'payment_gateway_invalid_response');
        }

        return $payload;
    }

    private function providerPayload(Response $response, string $operation): array
    {
        $payload = $this->json($response);

        if (($payload['result'] ?? null) === 'fail') {
            throw new PaymentGatewayException(
                errorCode: 'payment_gateway_rejected',
                context: ['operation' => $operation],
            );
        }

        return $payload;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('paykeeper.base_url'), '/').'/'.ltrim($path, '/');
    }
}
