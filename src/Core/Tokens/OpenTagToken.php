<?php

namespace Vasoft\Joke\Templator\Core\Tokens;

final class OpenTagToken extends Token
{
    /**
     * @param string $tagName Имя тега без префикса
     * @param array<string, string> $attributes Атрибуты вида key => value
     * @param string $raw Исходная строка тега
     */
    public function __construct(
        public string $tagName,
        public array $attributes,
        public string $raw
    ) {}
}