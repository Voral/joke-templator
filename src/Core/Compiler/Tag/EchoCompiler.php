<?php

namespace Vasoft\Joke\Templator\Core\Compiler\Tag;

use Vasoft\Joke\Templator\Contracts\Core\Compiler\CompilerInterface;
use Vasoft\Joke\Templator\Contracts\Core\Compiler\TagCompilerInterface;
use Vasoft\Joke\Templator\Core\Ast\TagNode;

class EchoCompiler implements TagCompilerInterface
{

    /**
     * @inheritDoc
     */
    public function getTagName(): string
    {
        return 'echo';
    }

    /**
     * @inheritDoc
     */
    public function compile(TagNode $node, CompilerInterface $compiler): string
    {
        if (!isset($node->attributes['value'])) {
            throw new \Exception("Missing 'value' attribute in <{$node->fullTagName}>");
        }
        $path = $this->generateArrayAccess($node->attributes['value']);
        return "echo htmlspecialchars({$path}, ENT_QUOTES, 'UTF-8');\n";
    }

    private function generateArrayAccess(string $path): string
    {
        $keys = explode('.', $path);
        $code = '$context';
        foreach ($keys as $key) {
            $code .= "['" . addslashes($key) . "']";
        }
        return $code;
    }
}