<?php

namespace App\Exceptions;

class ChatRagException extends BaseException
{
    public function __construct(
        string $message = 'ChatRAG provider error',
        string $errorCode = 'chatrag_error',
        array $context = [],
    ) {
        parent::__construct($message, 502, $errorCode, 'chatrag', $context);
    }
}
