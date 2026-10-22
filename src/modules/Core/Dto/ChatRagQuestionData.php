<?php

namespace Modules\Core\Dto;

final readonly class ChatRagQuestionData
{
    /**
     * @param  list<array{role: string, content: string}>|null  $history
     */
    public function __construct(
        public string $question,
        public ?string $conversationId = null,
        public ?array $history = null,
        public ?string $systemPrompt = null,
    ) {
    }
}
