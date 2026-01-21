<?php

namespace Vasoft\Joke\Templator\Tests\Contracts\Core\Ast;

use Vasoft\Joke\Templator\Contracts\Core\Ast\RawHandler;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Tests\Fixtures\MockRenderer;

class RawHandlerTest extends TestCase
{
    public function testRawHandlerDoesNotEscapeHtml(): void
    {
        $handler = new RawHandler();
        $renderer = new MockRenderer();
        $node = new TagNode('raw', 'j-raw', ['value' => 'content'], []);
        $context = ['content' => '<b>Bold</b>'];

        $result = $handler->handle($node, $context, $renderer);
        self::assertSame('<b>Bold</b>', $result); // ← Без экранирования
    }
}
