<?php

namespace Vasoft\Joke\Templator\Core\Tokens;

final class SelfClosingTagToken extends Token
{
    /**
     * @param string $tagName Имя тега без префикса
     * @param string $fullTagName Имя тега с префиксом
     * @param array<string, string|bool> $attributes атрибуты тега key => value
     * @param string $raw Исходная строка тега
     */
    public function __construct(
        public string $tagName,
        public string $fullTagName,
        public array $attributes,
        public string $raw
    ) {}
}