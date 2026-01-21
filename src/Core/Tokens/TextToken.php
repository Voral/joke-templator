<?php

namespace Vasoft\Joke\Templator\Core\Tokens;

final class TextToken extends Token
{
    public function __construct(public string $raw) { }
}