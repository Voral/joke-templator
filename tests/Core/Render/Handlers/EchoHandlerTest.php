<?php

namespace Vasoft\Joke\Templator\Tests\Core\Render\Handlers;

use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Core\Render\Handlers\EchoHandler;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Exceptions\RenderingException;
use Vasoft\Joke\Templator\Tests\Fixtures\MockRenderer;

class EchoHandlerTest extends TestCase
{
    private EchoHandler $handler;
    private MockRenderer $renderer;

    protected function setUp(): void
    {
        $this->handler = new EchoHandler();
        $this->renderer = new MockRenderer();
    }

    public function testEchoRendersStringValue(): void
    {
        $node = new TagNode('echo', 'j-echo', ['value' => 'name'], []);
        $context = ['name' => 'Alice'];

        $result = $this->handler->handle($node, $context, $this->renderer);
        self::assertSame('Alice', $result);
    }

    public function testEchoEscapesHtml(): void
    {
        $node = new TagNode('echo', 'j-echo', ['value' => 'content', 'escaped' => true], []);
        $context = ['content' => '<script>alert("xss")</script>'];

        $result = $this->handler->handle($node, $context, $this->renderer);
        self::assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $result);
    }

    public function testEchoReturnsEmptyForMissingValue(): void
    {
        $node = new TagNode('echo', 'j-echo', ['value' => 'missing'], []);
        $context = [];

        $result = $this->handler->handle($node, $context, $this->renderer);
        self::assertSame('', $result);
    }

    public function testEchoRequiresValueAttribute(): void
    {
        self::expectException(RenderingException::class);
        self::expectExceptionMessage("Attribute 'value' is required for <j-echo>");

        $node = new TagNode('echo', 'j-echo', [], []);
        $this->handler->handle($node, [], $this->renderer);
    }

    public function testEchoHandlesNestedPath(): void
    {
        $node = new TagNode('echo', 'j-echo', ['value' => 'user.name'], []);
        $context = ['user' => ['name' => 'Bob']];

        $result = $this->handler->handle($node, $context, $this->renderer);
        self::assertSame('Bob', $result);
    }
}
