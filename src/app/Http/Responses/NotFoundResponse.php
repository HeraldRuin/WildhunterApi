<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class NotFoundResponse extends JsonResponse
{
    public function __construct(
        private readonly string $message = '',
        private readonly ?string $code = 'not_found',
        private readonly ?string $domain = null,
        private readonly array $replace = [],
        int $status = 404,
    ) {
        parent::__construct([
            'success' => false,
            'message' => $this->resolveMessage(),
            'error_code' => $this->code ?? 'not_found',
        ], $status);
    }

    private function resolveMessage(): string
    {
        if ($this->code && $this->domain) {
            return __($this->domain . '.errors.' . $this->code, $this->replace);
        }
        return $this->message;
    }
}
