<?php

namespace Tests\Unit\Core;

use Modules\Core\Services\BlogContentParser;
use PHPUnit\Framework\TestCase;

class BlogContentParserTest extends TestCase
{
    public function test_faq_block_is_normalized_and_other_types_keep_their_type(): void
    {
        $blocks = (new BlogContentParser())->parse([
            'blocks' => [
                [
                    'id' => 'blk_text',
                    'type' => 'text',
                    'content' => '<p>Текст статьи</p>',
                ],
                [
                    'type' => 'faq',
                    'items' => [
                        ['id' => 'q1', 'question' => '  Вопрос?  ', 'answer' => ' Ответ. '],
                        ['question' => '   ', 'answer' => ''],
                        'broken',
                    ],
                ],
                ['type' => ''],
                'broken',
            ],
        ]);

        $this->assertSame([
            [
                'type' => 'text',
                'content' => '<p>Текст статьи</p>',
            ],
            [
                'type' => 'faq',
                'items' => [
                    ['question' => 'Вопрос?', 'answer' => 'Ответ.'],
                ],
            ],
        ], $blocks);
    }

    public function test_empty_faq_block_is_skipped(): void
    {
        $blocks = (new BlogContentParser())->parse([
            'blocks' => [
                ['type' => 'faq', 'items' => []],
            ],
        ]);

        $this->assertSame([], $blocks);
    }
}
