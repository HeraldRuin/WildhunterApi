<?php

namespace Modules\Core\Services;

class BlogContentParser
{
    public function parse(mixed $content): array
    {
        if (!is_array($content)) {
            return [];
        }

        $blocks = $content['blocks'] ?? [];
        if (!is_array($blocks)) {
            return [];
        }

        $parsed = [];
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $parsedBlock = $this->parseBlock($block);
            if ($parsedBlock !== null) {
                $parsed[] = $parsedBlock;
            }
        }

        return $parsed;
    }

    private function parseBlock(array $block): ?array
    {
        $type = $block['type'] ?? null;
        if (!is_string($type) || $type === '') {
            return null;
        }

        return match ($type) {
            'faq' => $this->parseFaq($block),
            default => $this->parsePassthrough($block),
        };
    }

    private function parseFaq(array $block): ?array
    {
        $items = [];
        foreach ($block['items'] ?? [] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question === '' && $answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        if ($items === []) {
            return null;
        }

        return [
            'type' => 'faq',
            'items' => $items,
        ];
    }

    private function parsePassthrough(array $block): array
    {
        unset($block['id']);

        return $block;
    }
}
