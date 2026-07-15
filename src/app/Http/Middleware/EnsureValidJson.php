<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidJson
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->expectsJsonBody($request)) {
            return $next($request);
        }

        $content = trim($request->getContent());

        if ($content === '') {
            return $next($request);
        }

        json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'error_code' => 'validation_error',
                'errors' => [
                    'body' => [__('common.invalid_json_body')],
                ],
                'trace_id' => $request->attributes->get('trace_id'),
            ], 422);
        }

        return $next($request);
    }

    private function expectsJsonBody(Request $request): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return str_contains(strtolower($request->header('Content-Type', '')), 'application/json');
    }
}
