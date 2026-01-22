<?php

namespace Vasoft\Joke\Templator\Core\Render\Handlers;

use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;

/**
 * Вывод не экранированного HTML
 */
class RawHandler extends BaseHandler
{

    public function __construct()
    {
        $this->requiredAttributes = ['value'];
    }

    /**
     * Опасный вывод: данные НЕ экранируются.
     * Используйте ТОЛЬКО для доверенного HTML.
     */
    protected function process(TagNode $node, array $context, RendererInterface $renderer): string
    {
        $value = $this->resolveValue($context, $node->attributes['value'], '');
        return (string)$value;
    }
}