<?php

namespace Vasoft\Joke\Templator\Tests\Core\Lexer;

use PHPUnit\Framework\Attributes\DataProvider;
use Vasoft\Joke\Templator\Core\Lexer\DefaultLexer;
use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Core\Tokens\CloseTagToken;
use Vasoft\Joke\Templator\Core\Tokens\OpenTagToken;
use Vasoft\Joke\Templator\Core\Tokens\SelfClosingTagToken;
use Vasoft\Joke\Templator\Core\Tokens\TextToken;
use Vasoft\Joke\Templator\Exceptions\LexerException;

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

    public function testSelfClosingTag(): void
    {
        $tokens = self::$defaultLexer->tokenize('<j-echo value="name"/>');
        self::assertCount(1, $tokens);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[0]);
        self::assertSame(['value' => 'name'], $tokens[0]->attributes);
    }

    public function testSelfClosingTagEmpty(): void
    {
        $tokens = self::$defaultLexer->tokenize('<j-echo/>');
        self::assertCount(1, $tokens);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[0]);
    }

    public function testNestedTags(): void
    {
        $tpl = "<j-if><j-echo value='x'/></j-if>";
        $tokens = self::$defaultLexer->tokenize($tpl);
        self::assertCount(3, $tokens);
        self::assertInstanceOf(OpenTagToken::class, $tokens[0]);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[1]);
        self::assertInstanceOf(CloseTagToken::class, $tokens[2]);
        self::assertSame(['value' => 'x'], $tokens[1]->attributes);
    }

    public function testUnclosedAttributeQuote(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Unclosed quote for attribute 'value'");
        self::$defaultLexer->tokenize('<j-echo value="name/>');
    }

    public function testNotQuotedAttribute(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Attribute value must be quoted at position 14");
        self::$defaultLexer->tokenize('<j-echo value=name/><a></a>');
    }

    public function testAttributeUnexpectedEnd(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Unexpected end after attribute 'value'");
        self::$defaultLexer->tokenize('<j-echo value=');
    }

    public function testNotClosedTag(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Expected '>' at position 17");
        self::$defaultLexer->tokenize('<j-echo value="1"');
    }

    public function testBooleanAttribute(): void
    {
        $tokens = self::$defaultLexer->tokenize('<j-echo value="1"/><j-echo value />');
        self::assertCount(2, $tokens);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[0]);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[1]);
        self::assertSame(['value' => true], $tokens[1]->attributes);
    }

    public function testEmptyTagName(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Empty tag name at position 0");
        self::$defaultLexer->tokenize('<j- />');
    }

    public function testUnclosedCloseTag(): void
    {
        self::expectException(LexerException::class);
        self::expectExceptionMessage("Unclosed closing tag starting at position 19");
        self::$defaultLexer->tokenize('<j-test><div></div></j-test');
    }

    public function testWithoutAttribute(): void
    {
        $tokens = self::$defaultLexer->tokenize('<j-echo />');
        self::assertCount(1, $tokens);
        self::assertInstanceOf(SelfClosingTagToken::class, $tokens[0]);
        self::assertSame([], $tokens[0]->attributes);
    }

    public function testStartsWithPrefixOffsetBeyondLength(): void
    {
        $lexer = new DefaultLexer();

        $method = new \ReflectionMethod(DefaultLexer::class, 'startsWithPrefix');
        $method->setAccessible(true);

        $template = '<j-echo/>';
        $length = strlen($template); // 10

        $result = $method->invokeArgs($lexer, [$template, $length, 10]);

        self::assertFalse($result);
    }

    public static function invalidAttributeNamesProvider(): array
    {
        return [
            ['123="value"'],   // начинается с цифры
            ['-name="val"'],   // начинается с дефиса
            [' name="val"'],   // начинается с пробела (но такого быть не должно, т.к. пробелы уже пропущены)
            ['"value"'],       // кавычки без имени
            ['=value'],        // знак равенства без имени
            [''],              // пустая строка
        ];
    }

    #[DataProvider('invalidAttributeNamesProvider')]
    public function testParseAttributesReturnsFalseForVariousInvalidInputs(string $fragment): void
    {
        $lexer = new DefaultLexer();
        $method = new \ReflectionMethod(DefaultLexer::class, 'parseAttributes');
        $method->setAccessible(true);

        $len = strlen($fragment);
        $pos = 0;
        $attrs = [];

        $result = $method->invokeArgs($lexer, [$fragment, $len, &$pos, &$attrs]);

        self::assertFalse($result);
        self::assertEmpty($attrs);
        self::assertSame(0, $pos);
    }

    public function testBreakInAttributeParsingLeadsToException(): void
    {
        $lexer = new DefaultLexer();

        self::expectException(LexerException::class);
        self::expectExceptionMessageMatches('/Expected \'>\' at position \d+/');

        $lexer->tokenize('<j-tag ok="1" @>');
    }

    /**
     * Проверяем что не нарушается внутренний HTML и идет одним токеном
     * @return void
     * @throws LexerException
     */
    public function testInternalHtml(): void
    {
        $tokens = self::$defaultLexer->tokenize('<j-each><p>Test</p></j-each>');
        self::assertCount(3, $tokens);
        self::assertInstanceOf(OpenTagToken::class, $tokens[0]);
        self::assertInstanceOf(TextToken::class, $tokens[1]);
        self::assertInstanceOf(CloseTagToken::class, $tokens[2]);

        self::assertSame('<p>Test</p>', $tokens[1]->raw);
    }

}
