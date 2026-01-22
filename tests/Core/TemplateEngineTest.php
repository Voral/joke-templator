<?php

namespace Vasoft\Joke\Templator\Tests\Core;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Core\ServiceContainer;
use Vasoft\Joke\Templator\Contracts\Core\Ast\ParserInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\RendererInterface;
use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;
use Vasoft\Joke\Templator\Contracts\Core\LexerInterface;
use Vasoft\Joke\Templator\Core\Ast\DefaultParser;
use Vasoft\Joke\Templator\Core\Ast\TagNode;
use Vasoft\Joke\Templator\Core\Lexer\DefaultLexer;
use Vasoft\Joke\Templator\Core\Render\DefaultRenderer;
use Vasoft\Joke\Templator\Core\Render\Handlers\EchoHandler;
use Vasoft\Joke\Templator\Core\Render\Handlers\RawHandler;
use Vasoft\Joke\Templator\Core\TemplateEngine;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;

class TemplateEngineTest extends TestCase
{
    use PHPMock;

    private TemplateEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new TemplateEngine(new ServiceContainer());
        $this->engine->registerTag('echo', new EchoHandler());
        $this->engine->registerTag('raw', new RawHandler());
    }

    public function testRenderStringWithEcho(): void
    {
        $template = 'Hello <j-echo value="name"/>!';
        $context = ['name' => '<b>Alice</b>'];

        $result = $this->engine->renderString($template, $context);
        self::assertSame('Hello &lt;b&gt;Alice&lt;/b&gt;!', $result);
    }

    public function testRenderStringWithRaw(): void
    {
        $template = 'Hello <j-raw value="content"/>!';
        $context = ['content' => '<b>Bold</b>'];

        $result = $this->engine->renderString($template, $context);
        self::assertSame('Hello <b>Bold</b>!', $result);
    }

    public function testRenderFile(): void
    {
        $templateFile = dirname(__DIR__, 1) . '/Fixtures/test.joke';
        file_put_contents($templateFile, 'Name: <j-echo value="name"/>');

        try {
            $result = $this->engine->renderFile($templateFile, ['name' => 'Bob']);
            self::assertSame('Name: Bob', $result);
        } finally {
            unlink($templateFile);
        }
    }

    public function testThrowsExceptionOnUnknownTag(): void
    {
        $this->expectException(TemplatorException::class);
        $this->engine->renderString('<j-unknown/>', []);
    }

    public function testThrowsExceptionOnMissingFile(): void
    {
        $this->expectException(TemplatorException::class);
        $this->expectExceptionMessage('Template file not found: /non/existent/file.joke');
        $this->engine->renderFile('/non/existent/file.joke', []);
    }

    public function testErrorRenderingTemplate(): void
    {
        $this->engine = new TemplateEngine(new ServiceContainer());
        $this->engine->registerTag(
            'error',
            new class implements TagHandlerInterface {

                public function handle(TagNode $node, array $context, RendererInterface $renderer): string
                {
                    throw new \Error('Some error');
                }
            }
        );

        self::expectException(TemplatorException::class);
        self::expectExceptionMessage('Error rendering template: Some error');
        $this->engine->renderString('<j-error/>', []);
    }

    #[RunInSeparateProcess]
    public function testErrorReadingTemplate(): void
    {
        $fileGetContentMock = $this->getFunctionMock('Vasoft\Joke\Templator\Core', 'file_get_contents');
        $fileExistsMock = $this->getFunctionMock('Vasoft\Joke\Templator\Core', 'file_exists');
        $fileExistsMock->expects(self::once())->willReturn(true);
        $fileGetContentMock->expects(self::once())->willReturn(false);
        $this->expectException(TemplatorException::class);
        $this->expectExceptionMessage('Unable to read template file: /existent/file.joke');
        $this->engine->renderFile('/existent/file.joke', []);
    }

    public function testUsesDefaultImplementationsWhenNotInContainer(): void
    {
        $container = new ServiceContainer();
        $engine = new TemplateEngine($container);

        $reflection = new \ReflectionClass($engine);

        $lexerProp = $reflection->getProperty('lexer');
        $lexerProp->setAccessible(true);
        $lexer = $lexerProp->getValue($engine);
        self::assertInstanceOf(DefaultLexer::class, $lexer);

        $parserProp = $reflection->getProperty('parser');
        $parserProp->setAccessible(true);
        $parser = $parserProp->getValue($engine);
        self::assertInstanceOf(DefaultParser::class, $parser);

        $rendererProp = $reflection->getProperty('renderer');
        $rendererProp->setAccessible(true);
        $renderer = $rendererProp->getValue($engine);
        self::assertInstanceOf(DefaultRenderer::class, $renderer);
    }

    public function testUsesContainerImplementationsWhenRegistered(): void
    {
        $container = new ServiceContainer();

        $stubLexer = new class implements LexerInterface {
            public function tokenize(string $template): array { return []; }
        };

        $stubParser = new class implements ParserInterface {
            public function parse(array $tokens): array { return []; }
        };

//        $stubRenderer = new class implements RendererInterface {
//            public function registerTag(string $tagName, TagHandlerInterface $handler): RendererInterface {
//                return $this;
//            }
//            public function render(array $nodes, array $context): string { return ''; }
//        };

        $container->registerSingleton(LexerInterface::class, $stubLexer);
        $container->register(ParserInterface::class, $stubParser);
//        $container->registerSingleton(RendererInterface::class, $stubRenderer);

        $engine = new TemplateEngine($container);

        $reflection = new \ReflectionClass($engine);


        $lexerProp = $reflection->getProperty('lexer');
        $lexerProp->setAccessible(true);
        self::assertSame($stubLexer, $lexerProp->getValue($engine));

        $parserProp = $reflection->getProperty('parser');
        $parserProp->setAccessible(true);
        self::assertSame($stubParser, $parserProp->getValue($engine));

//        $rendererProp = $reflection->getProperty('renderer');
//        $rendererProp->setAccessible(true);
//        self::assertSame($stubRenderer, $rendererProp->getValue($engine));
    }
}
