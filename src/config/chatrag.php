<?php

return [
    'base_url' => env('CHATRAG_BASE_URL', 'https://chatrag.ru'),
    'api_key' => env('CHATRAG_API_KEY', ''),
    'ask_path' => env('CHATRAG_ASK_PATH', '/api/v1/ask'),
    'timeout' => (int) env('CHATRAG_TIMEOUT', 30),
    'connect_timeout' => (int) env('CHATRAG_CONNECT_TIMEOUT', 5),
];
