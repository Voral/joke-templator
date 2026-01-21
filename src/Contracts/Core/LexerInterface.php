<?php

namespace Vasoft\Joke\Templator\Contracts\Core;

/**
 * Интерфейс лексера
 */
interface LexerInterface
{
    /**
     * Преобразует строку в список токенов
     * @param string $content исходный текст
     * @return array<TokenInterface> список токенов
     */
    public function tokenize(string $content): array;
}