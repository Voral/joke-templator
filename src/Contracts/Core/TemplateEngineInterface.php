<?php

namespace Vasoft\Joke\Templator\Contracts\Core;

use Vasoft\Joke\Templator\Contracts\Core\Ast\TagHandlerInterface;

/**
 * Шаблонизатор
 */
interface TemplateEngineInterface
{
    /**
     * Регистрирует обработчик для тега.
     *
     * @param string $tagName Имя тега (без префикса)
     * @param TagHandlerInterface $handler
     */
    public function registerTag(string $tagName, TagHandlerInterface $handler): void;

    /**
     * Рендерит шаблон из строки.
     *
     * @param string $template
     * @param array<string, mixed> $context
     * @return string
     */
    public function renderString(string $template, array $context): string;

    /**
     * Рендерит шаблон из файла.
     *
     * @param string $path
     * @param array<string, mixed> $context
     * @return string
     */
    public function renderFile(string $path, array $context): string;
}