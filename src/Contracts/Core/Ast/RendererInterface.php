<?php

namespace Vasoft\Joke\Templator\Contracts\Core\Ast;

interface RendererInterface
{
    /**
     * Регистрирует обработчик для тега.
     *
     * @param string $tagName Имя тега (без префикса)
     * @param TagHandlerInterface $handler
     */
    public function registerTag(string $tagName, TagHandlerInterface $handler): void;

    /**
     * Рендерит AST в строку.
     *
     * @param array<NodeInterface> $nodes список узлов
     * @param array<string, mixed> $context Переменные шаблона
     * @return string
     */
    public function render(array $nodes, array $context): string;
}