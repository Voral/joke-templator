<?php

namespace Vasoft\Joke\Templator\Core\Tokens;

use Vasoft\Joke\Templator\Core\Lexer\Token;

/**
 * @deprecated
 */
final class CloseTagToken extends Token
{
    /**
     * @param string $tagName Имя закрываемого тега
     * @param string $fullTagName Закрываемый тег вместе с префиксом
     * @param string $raw Исходная строка
     */
    public function __construct(
        public string $tagName,
        public string $fullTagName,
        string $raw
    ) {
        parent::__construct($raw);
    }
}