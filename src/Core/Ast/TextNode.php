<?php

namespace Vasoft\Joke\Templator\Core\Ast;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;

/**
 * Текстовый узел AST дерева
 */
class TextNode implements NodeInterface
{
    /**
     * @param string $content Текст
     */
    public function __construct(public string $content) { }
}