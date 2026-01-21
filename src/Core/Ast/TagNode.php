<?php

namespace Vasoft\Joke\Templator\Core\Ast;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;

/**
 * Узел тега AST дерева
 */
class TagNode implements NodeInterface
{
    /**
     * @param string $tagName Имя узла, без префикса шаблонизатора
     * @param string $fullTagName Полное имя с префиксом шаблонизатора
     * @param array<string,string|bool> $attributes Атрибут узла в виде key=>value
     * @param array $children Дочерние узлы
     * @param bool $selfClosing Признак самозакрывающегося тега
     */
    public function __construct(
        public string $tagName,
        public string $fullTagName,
        public array $attributes = [],
        public array $children = [],
        public bool $selfClosing = false
    ) {
    }
}