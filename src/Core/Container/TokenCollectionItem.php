<?php
declare(strict_types=1);

namespace Vasoft\Joke\Templator\Core\Container;

use Vasoft\Joke\Templator\Core\Lexer\TokenDescriptor;

/**
 * Описание токена в коллекции
 */
final class TokenCollectionItem
{
    /**
     * @param TokenDescriptor $descriptor Описание токена
     * @param int $sort Порядок обработки токена лексером
     */
    public function __construct(
        public readonly TokenDescriptor $descriptor,
        public int                      $sort
    )
    {
    }
}