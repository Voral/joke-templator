<?php

namespace Vasoft\Joke\Templator\Tests\Core\Render;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Core\Ast\TextNode;
use Vasoft\Joke\Templator\Core\Render\DefaultRenderer;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Exceptions\RenderingException;
use Vasoft\Joke\Templator\Tests\Fixtures\DummyTagHandler;

class DefaultRendererTest extends TestCase
{
    private DefaultRenderer $renderer;
    private DummyTagHandler $handler;

    protected function setUp(): void
    {
        $this->renderer = new DefaultRenderer();
        $this->handler = new DummyTagHandler();
    }

    public function testRenderTextNodes(): void
    {
        $nodes = [
            new TextNode('Hello '),
            new TextNode('World!')
        ];

        $result = $this->renderer->render($nodes, []);
        self::assertSame('Hello World!', $result);
    }

    public function testRenderTagNodesWithRegisteredHandler(): void
    {
        $this->renderer->registerTag('test', $this->handler);

        $nodes = [
            new TagNode('test', 'j-test', [], [])
        ];

        $context = ['key' => 'value'];
        $result = $this->renderer->render($nodes, $context);

        self::assertSame('[HANDLED:test]', $result);
        self::assertCount(1, $this->handler->calls);
        self::assertSame($context, $this->handler->calls[0]['context']);
        self::assertInstanceOf(DefaultRenderer::class, $this->handler->calls[0]['renderer']);
    }

    public function testRenderMixedNodes(): void
    {
        $this->renderer->registerTag('echo', $this->handler);

        $nodes = [
            new TextNode('Start'),
            new TagNode('echo', 'j-echo', [], []),
            new TextNode('End')
        ];

        $result = $this->renderer->render($nodes, []);
        self::assertSame('Start[HANDLED:echo]End', $result);
    }

    public function testThrowsExceptionForUnregisteredTag(): void
    {
        $this->expectException(RenderingException::class);
        $this->expectExceptionMessage("No handler registered for tag 'j-unknown'");

        $nodes = [new TagNode('unknown', 'j-unknown', [], [])];
        $this->renderer->render($nodes, []);
    }

    public function testThrowsExceptionForUnknownNodeType(): void
    {
        $this->expectException(RenderingException::class);
        $this->expectExceptionMessage('Unknown node type');

        $fakeNode = new class ( ) implements NodeInterface { };
        $this->renderer->render([$fakeNode], []);
    }

    public function testRegisterTagReturnsSelfForFluentInterface(): void
    {
        $result = $this->renderer->registerTag('test', $this->handler);
        self::assertSame($this->renderer, $result);
    }

    public function testRenderEmptyNodeList(): void
    {
        $result = $this->renderer->render([], []);
        self::assertSame('', $result);
    }
}
