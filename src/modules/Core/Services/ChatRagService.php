<?php

namespace Modules\Core\Services;

use App\Exceptions\ChatRagException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Core\Dto\ChatRagQuestionData;
use Throwable;

class ChatRagService
{
    /**
     * @return array<string, mixed>
     */
    public function ask(ChatRagQuestionData $question): array
    {
        $body = ['question' => $question->question, 'source' => 'api'];

        if ($question->conversationId !== null) {
            $body['conversation_id'] = $question->conversationId;
        }

        if ($question->history !== null) {
            $body['history'] = $question->history;
        }

        if ($question->systemPrompt !== null) {
            $body['system_prompt'] = $question->systemPrompt;
        }

        return $this->json($this->send('post', (string) config('chatrag.ask_path'), $body));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function send(string $method, string $path, array $data = []): Response
    {
        try {
            $response = $this->client()->{$method}($this->url($path), $data);
        } catch (Throwable $exception) {
            throw new ChatRagException(
                message: $exception->getMessage(),
                context: ['operation' => $path],
            );
        }

        if (!$response->successful()) {
            throw new ChatRagException(
                context: ['operation' => $path, 'provider_status' => $response->status()],
            );
        }

        return $response;
    }

    private function client(): PendingRequest
    {
        $apiKey = (string) config('chatrag.api_key');

        if ($apiKey === '') {
            throw new ChatRagException(errorCode: 'chatrag_not_configured');
        }

        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['X-API-Key' => $apiKey])
            ->connectTimeout((int) config('chatrag.connect_timeout'))
            ->timeout((int) config('chatrag.timeout'))
            ->retry(2, 200, throw: false);
    }

    /**
     * @return array<string, mixed>
     */
    private function json(Response $response): array
    {
        $payload = $response->json();

        if (!is_array($payload)) {
            throw new ChatRagException(errorCode: 'chatrag_invalid_response');
        }

        return $payload;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('chatrag.base_url'), '/').'/'.ltrim($path, '/');
    }
}
