<?php

namespace Vasoft\Joke\Templator\Core;

use Vasoft\Joke\Core\ServiceContainer;
use Vasoft\Joke\Templator\Contracts\Core\Ast\ParserInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;
use Vasoft\Joke\Templator\Contracts\Core\LexerInterface;
use Vasoft\Joke\Templator\Contracts\Core\TemplateEngineInterface;
use Vasoft\Joke\Templator\Core\Ast\DefaultParser;
use Vasoft\Joke\Templator\Core\Lexer\DefaultLexer;
use Vasoft\Joke\Templator\Core\Render\DefaultRenderer;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;

class TemplateEngine implements TemplateEngineInterface
{

    private ?LexerInterface $lexer;
    private ?ParserInterface $parser;
    private ?RendererInterface $renderer;

    public function __construct(ServiceContainer $serviceContainer)
    {
        $this->lexer = $serviceContainer->get(LexerInterface::class);
        if ($this->lexer === null) {
            $this->lexer = new DefaultLexer();
        }
        $this->parser = $serviceContainer->get(ParserInterface::class);
        if ($this->parser === null) {
            $this->parser = new DefaultParser();
        }
        $this->renderer = $serviceContainer->get(RendererInterface::class);
        if ($this->renderer === null) {
            $this->renderer = new DefaultRenderer();
        }
    }

    /**
     * @inheritDoc
     */
    public function registerTag(string $tagName, TagHandlerInterface $handler): void
    {
        $this->renderer->registerTag($tagName, $handler);
    }

    /**
     * @inheritDoc
     */
    public function renderString(string $template, array $context): string
    {
        try {
            $tokens = $this->lexer->tokenize($template);
            $ast = $this->parser->parse($tokens);
            return $this->renderer->render($ast, $context);
        } catch (\Throwable $e) {
            if ($e instanceof TemplatorException) {
                throw $e;
            }
            throw new TemplatorException('Error rendering template: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function renderFile(string $path, array $context): string
    {
        if (!file_exists($path)) {
            throw new TemplatorException("Template file not found: $path");
        }
        $template = file_get_contents($path);
        if ($template === false) {
            throw new TemplatorException("Unable to read template file: $path");
        }
        return $this->renderString($template, $context);
    }

}