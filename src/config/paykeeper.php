<?php

return [
    'base_url' => rtrim((string) env('PAYKEEPER_BASE_URL', ''), '/'),
    'client_id' => env('PAYKEEPER_CLIENT_ID'),
    'client_secret' => env('PAYKEEPER_CLIENT_SECRET'),
    'token_path' => env('PAYKEEPER_TOKEN_PATH', '/info/settings/token/'),
    'invoice_path' => env('PAYKEEPER_INVOICE_PATH', '/change/invoice/preview/'),
    'revoke_path' => env('PAYKEEPER_REVOKE_PATH', '/change/invoice/revoke/'),
    'status_path' => env('PAYKEEPER_STATUS_PATH', '/info/invoice/byid/'),
    'currency' => env('PAYKEEPER_CURRENCY', 'RUB'),
    'invoice_ttl_minutes' => (int) env('PAYKEEPER_INVOICE_TTL_MINUTES', 30),
    'token_ttl_minutes' => (int) env('PAYKEEPER_TOKEN_TTL_MINUTES', 10),
    'cache_key' => env('PAYKEEPER_CACHE_KEY', 'paykeeper:token'),
    'timeout' => (int) env('PAYKEEPER_TIMEOUT', 10),
    'connect_timeout' => (int) env('PAYKEEPER_CONNECT_TIMEOUT', 5),
    'retry_delays' => [60, 120, 300, 600, 900],
];
