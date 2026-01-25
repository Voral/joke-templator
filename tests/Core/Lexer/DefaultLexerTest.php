<?php

namespace Vasoft\Joke\Templator\Tests\Core\Lexer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Core\Lexer\DefaultLexer;
use Vasoft\Joke\Templator\Core\Lexer\TextToken;
use Vasoft\Joke\Templator\Core\Tokens\CloseTagToken;
use Vasoft\Joke\Templator\Core\Tokens\OpenTagToken;
use Vasoft\Joke\Templator\Core\Tokens\SelfClosingTagToken;
use Vasoft\Joke\Templator\Exceptions\LexerException;

#[Group("skip")]
class DefaultLexerTest extends TestCase
{
    private static ?DefaultLexer $defaultLexer = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$defaultLexer = new DefaultLexer();
    }

    public function testTextOnly(): void
    {
        $tokens = self::$defaultLexer->tokenize('Hello world');
        self::assertCount(1, $tokens);
        self::assertInstanceOf(TextToken::class, $tokens[0]);
        self::assertSame('Hello world', $tokens[0]->raw);
    }

    public function testMultipleTokens(): void
    {
        $tokens = self::$defaultLexer->tokenize('{{test}}Hello world {%example%}{% /example %} {{ test }}');
        self::assertCount(6, $tokens);
    }
}
