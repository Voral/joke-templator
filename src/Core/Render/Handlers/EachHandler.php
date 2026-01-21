<?php

namespace Vasoft\Joke\Templator\Core\Render\Handlers;

use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Exceptions\RenderingException;

class EachHandler extends BaseHandler implements TagHandlerInterface
{
    public function __construct()
    {
        $this->requiredAttributes = ['items', 'as'];
    }

    /**
     * @inheritDoc
     * @throws RenderingException
     */
    protected function process(TagNode $node, array $context, RendererInterface $renderer): string
    {
        $itemsPath = $node->attributes['items'];
        $as = $node->attributes['as'];

        $items = $this->resolveValue($context, $itemsPath, null);

        if (!is_array($items)) {
            throw new RenderingException("Value at path '$itemsPath' is not an array for <{$node->fullTagName}>");
        }

        $output = '';
        foreach ($items as $item) {
            $iterationContext = $context;
            $iterationContext[$as] = $item;

            $output .= $renderer->render($node->children, $iterationContext);
        }

        return $output;
    }
}