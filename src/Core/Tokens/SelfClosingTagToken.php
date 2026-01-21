<?php

namespace Vasoft\Joke\Templator\Core\Tokens;

final class SelfClosingTagToken extends Token
{
    /**
     * @param string $tagName Имя тега без префикса
     * @param array<string, string> $attributes атрибуты тега key => value
     * @param string $raw Исходная строка тега
     */
    public function __construct(
        public string $tagName,
        public array $attributes,
        public string $raw
    ) {}
}