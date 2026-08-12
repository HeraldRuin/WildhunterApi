<?php

namespace App\Exceptions;

class PaymentGatewayException extends BaseException
{
    public function __construct(
        string $message = 'Payment provider error',
        string $errorCode = 'payment_gateway_error',
        array $context = [],
    ) {
        parent::__construct($message, 502, $errorCode, 'payment', $context);
    }
}
