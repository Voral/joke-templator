<?php

namespace Vasoft\Joke\Templator\Core\Compiler\Node;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\CompilerInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\NodeCompilerInterface;
use Vasoft\Joke\Templator\Core\Ast\TextNode;

class TextNodeCompiler implements NodeCompilerInterface
{

    /**
     * @inheritDoc
     */
    public function supports(NodeInterface $node): bool
    {
        return $node instanceof TextNode;
    }

    /**
     * @inheritDoc
     * @param TextNode $node
     */
    public function compile(NodeInterface $node, CompilerInterface $compiler): string
    {
        return 'echo ' . var_export($node->content, true) . ";\n";
    }
}