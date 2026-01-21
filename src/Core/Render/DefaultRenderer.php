<?php

namespace Vasoft\Joke\Templator\Core\Render;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Core\Ast\TextNode;
use Vasoft\Joke\Templator\Exceptions\RenderingException;

class DefaultRenderer implements RendererInterface
{
    /**
     * @var array<string, TagHandlerInterface>
     */
    private array $handlers = [];

    /**
     * @inheritDoc
     */
    public function registerTag(string $tagName, TagHandlerInterface $handler): static
    {
        $this->handlers[$tagName] = $handler;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws RenderingException
     */
    public function render(array $nodes, array $context): string
    {
        $output = '';
        foreach ($nodes as $node) {
            $output .= $this->renderNode($node, $context);
        }
        return $output;
    }


    /**
     * @param NodeInterface $node
     * @param array<string, mixed> $context
     * @return string
     * @throws RenderingException
     */
    private function renderNode(NodeInterface $node, array $context): string
    {
        if ($node instanceof TextNode) {
            return $node->content;
        }

        if ($node instanceof TagNode) {
            if (!isset($this->handlers[$node->tagName])) {
                throw new RenderingException(
                    "No handler registered for tag '{$node->fullTagName}'."
                );
            }

            $handler = $this->handlers[$node->tagName];
            return $handler->handle($node, $context, $this);
        }

        throw new RenderingException('Unknown node type: ' . get_class($node));
    }
}