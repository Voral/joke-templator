<?php

namespace Vasoft\Joke\Templator\Core\Lexer;

use Vasoft\Joke\Templator\Contracts\Core\LexerInterface;
use Vasoft\Joke\Templator\Contracts\Core\TokenInterface;
use Vasoft\Joke\Templator\Core\Tokens\CloseTagToken;
use Vasoft\Joke\Templator\Core\Tokens\OpenTagToken;
use Vasoft\Joke\Templator\Core\Tokens\SelfClosingTagToken;
use Vasoft\Joke\Templator\Core\Tokens\TextToken;
use Vasoft\Joke\Templator\Exceptions\LexerException;

class DefaultLexer implements LexerInterface
{
    private const string TAG_PREFIX = 'j-';
    private const int TAG_PREFIX_LENGTH = 2;

    /**
     * @inheritDoc
     * @throws LexerException
     */
    public function tokenize(string $template): array
    {
        /** @var list<TokenInterface> $tokens */
        $tokens = [];
        $templateLength = strlen($template);
        $pos = 0;
        while ($pos < $templateLength) {
            if (!$this->tokenizeTemplateTag($templateLength, $template, $pos, $tokens)) {
                $this->tokenizePlainText($templateLength, $template, $pos, $tokens);
            }
        }

        return $tokens;
    }

    /**
     * Поиск тега шаблонизатора
     * @param int $templateLength размер шаблона
     * @param string $template шаблон
     * @param int $pos текущая позиция
     * @param list<TokenInterface> $tokens список токенов
     */
    private function tokenizePlainText(int $templateLength, string $template, int &$pos, array &$tokens): void
    {
        $start = $pos;
        while ($pos < $templateLength && $template[$pos] !== '<') {
            ++$pos;
        }
        $text = substr($template, $start, $pos - $start);
        if ($text !== '') {
            $tokens[] = new TextToken($text);
        }
    }

    /**
     * Поиск тега шаблонизатора
     * @param int $templateLength размер шаблона
     * @param string $template шаблон
     * @param int $pos текущая позиция
     * @param list<TokenInterface> $tokens список токенов
     * @return bool
     * @throws LexerException
     */
    private function tokenizeTemplateTag(int $templateLength, string $template, int &$pos, array &$tokens): bool
    {
        if ($template[$pos] === '<') {
            if ($pos + 1 < $templateLength && $template[$pos + 1] === '/') {
                return $this->tokenizeCloseTag($template, $templateLength, $pos, $tokens);
            }
            return $this->tokenizeTag($template, $templateLength, $pos, $tokens);
        }
        ++$pos;
        return false;
    }

    /**
     * Поиск начала тега шаблонизатора
     * @param string $template шаблон
     * @param int $templateLength размер шаблона
     * @param int $pos текущая позиция
     * @param list<TokenInterface> $tokens список токенов
     * @return bool
     * @throws LexerException
     */
    private function tokenizeTag(string $template, int $templateLength, int &$pos, array &$tokens): bool
    {
        if ($this->startsWithPrefix($template, $templateLength, $pos + 1)) {
            $tokens[] = $this->parseTag($template, $templateLength, $pos);
            return true;
        }
        ++$pos;
        return false;
    }

    /**
     * Поиск закрытия тега шаблонизатора
     * @param string $template шаблон
     * @param int $templateLength размер шаблона
     * @param int $pos текущая позиция
     * @param list<TokenInterface> $tokens список токенов
     * @return bool
     * @throws LexerException в случае ошибок лексера
     */

    private function tokenizeCloseTag(string $template, int $templateLength, int &$pos, array &$tokens): bool
    {
        if ($this->startsWithPrefix($template, $templateLength, $pos + 2)) {
            $tokens[] = $this->parseCloseTag($template, $templateLength, $pos);
            return true;
        }
        ++$pos;
        return false;
    }

    /**
     * Проверяет, начинается ли подстрока с указанной позиции с $needle
     */
    private function startsWithPrefix(string $template, int $templateLength, int $offset): bool
    {
        if ($offset < 0 || $offset >= $templateLength) {
            return false;
        }
        return strncmp(substr($template, $offset), self::TAG_PREFIX, self::TAG_PREFIX_LENGTH) === 0;
    }

    /**
     * Парсит закрывающий тег: </j-name>
     *
     * @param string $template шаблон
     * @param int $templateLength размер шаблона
     * @param int $pos начальная позиция
     * @return CloseTagToken
     * @throws LexerException
     */
    private function parseCloseTag(string $template, int $templateLength, int &$pos): CloseTagToken
    {
        $i = $pos + 2 + self::TAG_PREFIX_LENGTH;
        $tagName = '';
        while ($i < $templateLength && $template[$i] !== '>') {
            $tagName .= $template[$i];
            $i++;
        }

        if ($i >= $templateLength) {
            throw new LexerException("Unclosed closing tag starting at position $pos");
        }

        $raw = substr($template, $pos, $i - $pos + 1);
        $i++;

        $pos = $i;
        return new CloseTagToken(trim($tagName), $raw);
    }

    /**
     * Парсит открывающий или самозакрывающийся тег: <j-name ... /> возвращает токен
     *
     * @param string $template шаблон
     * @param int $templateLength размер шаблона
     * @param int $pos текущая позиция
     * @return OpenTagToken|SelfClosingTagToken
     * @throws LexerException в случае ошибок парсинга
     */
    private function parseTag(string $template, int $templateLength, int &$pos): OpenTagToken|SelfClosingTagToken
    {
        $i = $pos + 1 + self::TAG_PREFIX_LENGTH;
        $tagName = '';

        // Читаем имя тега до первого пробела или >
        while ($i < $templateLength && !in_array($template[$i], [" ", "\t", "\n", "\r", ">"], true)) {
            $tagName .= $template[$i];
            $i++;
        }

        $tagName = trim($tagName);
        if ($tagName === '') {
            throw new LexerException("Empty tag name at position $pos");
        }

        $this->skipWhitespaces($template, $templateLength, $i);

        /** @var array<string, string> $attributes */
        $attributes = [];
        while ($i < $templateLength && $template[$i] !== '>' && $template[$i] !== '/') {
            if (!$this->parseAttributes($template, $templateLength, $i, $attributes)) {
                break;
            }
        }
        // Проверяем, самозакрывающийся ли тег
        $selfClosing = false;
        if ($i < $templateLength && $template[$i] === '/') {
            $selfClosing = true;
            $i++; // пропустить '/'
        }

        if ($i >= $templateLength || $template[$i] !== '>') {
            throw new LexerException("Expected '>' at position $i");
        }
        $raw = substr($template, $pos, $i - $pos + 1);
        $i++; // пропустить '>'

        $pos = $i;
        return $selfClosing
            ? new SelfClosingTagToken($tagName, $attributes, $raw)
            : new OpenTagToken($tagName, $attributes, $raw);
    }

    /**
     * @param string $template шаблон
     * @param int $i ссылка на текущую позицию
     * @param array $attributes ссылка на накапливаемый массив атрибутов
     * @return bool false - если атрибутов больше нет, true - еще возможны атрибуты
     * @throws LexerException в случае ошибки парсинга
     */
    private function parseAttributes(string $template, int $templateLength, int &$i, array &$attributes): bool
    {
        $matches = [];
        if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_-]*)(\s*=\s*)?/A', substr($template, $i), $matches)) {
            return false;
        }
        $attrName = $matches[1];

        if (isset($matches[2])) {
            $attributes[$attrName] = $this->parseQuotedAttribute($template, $templateLength, $i, $matches);
        } else {
            $i += strlen($matches[0]);
            $attributes[$attrName] = true;
        }
        $this->skipWhitespaces($template, $templateLength, $i);
        return true;
    }

    private function parseQuotedAttribute(string $template, int $templateLength, int &$i, array $matches): string
    {
        $i += strlen($matches[0]);

        // Определяем кавычки
        if ($i >= $templateLength) {
            throw new LexerException("Unexpected end after attribute '$matches[1]'");
        }

        $quote = $template[$i];
        if ($quote !== '"' && $quote !== "'") {
            throw new LexerException("Attribute value must be quoted at position $i");
        }
        $i++;

        $value = '';
        while ($i < $templateLength && $template[$i] !== $quote) {
            $value .= $template[$i];
            $i++;
        }

        if ($i >= $templateLength) {
            throw new LexerException("Unclosed quote for attribute '$matches[1]'");
        }
        $i++; // пропустить закрывающую кавычку

        return $value;
    }

    /**
     * Пропуск пробелов
     * @param string $template шаблон
     * @param int $templateLength размер шаблона
     * @param int $pos текущая позиция
     * @return void
     */
    private function skipWhitespaces(string $template, int $templateLength, int &$pos): void
    {
        while ($pos < $templateLength && in_array($template[$pos], [" ", "\t", "\n", "\r"], true)) {
            ++$pos;
        }
    }
}