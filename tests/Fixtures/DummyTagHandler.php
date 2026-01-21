<?php

namespace Vasoft\Joke\Templator\Tests\Fixtures;

use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;

class DummyTagHandler implements TagHandlerInterface
{
    public array $calls = [];

    public function handle(TagNode $node, array $context, RendererInterface $renderer): string
    {
        $this->calls[] = [
            'node' => $node,
            'context' => $context,
            'renderer' => $renderer
        ];
        return "[HANDLED:{$node->tagName}]";
    }
}