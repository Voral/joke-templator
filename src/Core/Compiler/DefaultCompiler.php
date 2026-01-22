<?php

namespace Vasoft\Joke\Templator\Core\Compiler;

use Vasoft\Joke\Templator\Contracts\Core\Ast\NodeInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\CompilerInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\NodeCompilerInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\TagCompilerInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;

class DefaultCompiler implements CompilerInterface
{
    /** @var array<string, string> $tagCompilers [tagName => compilerClass] */
    private array $tagCompilers = [];

    /** @var array<string, string> $nodeCompilers [nodeClass => compilerClass] */
    private array $nodeCompilers = [];

    /** @var array<string, NodeCompilerInterface> $instantiatedNodeCompilers */
    private array $instantiatedNodeCompilers = [];

    /** @var array<string, TagCompilerInterface> $instantiatedTagCompilers */
    private array $instantiatedTagCompilers = [];

    public function registerTagCompiler(string $tagName, string $compilerClass): static
    {
        if (!is_a($compilerClass, TagCompilerInterface::class, true)) {
            throw new \InvalidArgumentException("Compiler must implement TagCompilerInterface");
        }
        $this->tagCompilers[$tagName] = $compilerClass;
        return $this;
    }

    public function registerNodeCompiler(string $nodeClass, string $compilerClass): static
    {
        if (!is_a($compilerClass, NodeCompilerInterface::class, true)) {
            throw new \InvalidArgumentException("Compiler must implement NodeCompilerInterface");
        }
        $this->nodeCompilers[$nodeClass] = $compilerClass;
        return $this;
    }

    public function compile(array $ast): string
    {
        $code = "<?php\n";
        foreach ($ast as $node) {
            $code .= $this->compileNode($node);
        }
        return $code . "?>";
    }

    public function compileNode(NodeInterface $node): string
    {
        // Сначала пробуем NodeCompiler'ы по конкретному классу
        $nodeClass = get_class($node);
        if (isset($this->nodeCompilers[$nodeClass])) {
            $compiler = $this->getNodeCompiler($nodeClass);
            return $compiler->compile($node, $this);
        }

        // Затем пробуем TagCompiler для TagNode
        if ($node instanceof TagNode) {
            if (isset($this->tagCompilers[$node->tagName])) {
                $compiler = $this->getTagCompiler($node->tagName);
                return $compiler->compile($node, $this);
            }
            throw new \Exception("No compiler registered for tag '{$node->fullTagName}'");
        }

        throw new \Exception('No compiler found for node: ' . $nodeClass);
    }

    private function getNodeCompiler(string $nodeClass): NodeCompilerInterface
    {
        if (!isset($this->instantiatedNodeCompilers[$nodeClass])) {
            $class = $this->nodeCompilers[$nodeClass];
            $this->instantiatedNodeCompilers[$nodeClass] = new $class();
        }
        return $this->instantiatedNodeCompilers[$nodeClass];
    }

    private function getTagCompiler(string $tagName): TagCompilerInterface
    {
        if (!isset($this->instantiatedTagCompilers[$tagName])) {
            $class = $this->tagCompilers[$tagName];
            $this->instantiatedTagCompilers[$tagName] = new $class();
        }
        return $this->instantiatedTagCompilers[$tagName];
    }
}