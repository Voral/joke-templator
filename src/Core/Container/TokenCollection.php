<?php
declare(strict_types=1);

namespace Vasoft\Joke\Templator\Core\Container;

use Vasoft\Joke\Templator\Core\Lexer\TokenDescriptor;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;

/**
 * Коллекция для хранения токенов и подготовки списка для работы лексера
 */
class TokenCollection
{
    /**
     * @var array<string,TokenCollectionItem> Реестр описаний токенов
     */
    private array $items = [];
    /** @var list<TokenDescriptor> Подготовленный список описаний токенов */
    private array $descriptors = [];

    /**
     * Добавляет описание токена с проверкой на существование
     * @param TokenDescriptor $descriptor Описание токена
     * @param int $sort Порядок проверки токена лексером
     * @return $this
     * @throws TemplatorException Если токен с таким открывающим маркером уже существует
     */
    public function add(TokenDescriptor $descriptor, int $sort): static
    {
        if (array_key_exists($descriptor->open, $this->items)) {
            throw new TemplatorException('Token already exists');
        }
        $this->items[$descriptor->open] = new TokenCollectionItem($descriptor, $sort);
        return $this;
    }

    /**
     * Безусловное добавляет описание токена. Если токен с таким же открывающим маркером был уже зарегистрирован, то перезаписывается
     * @param TokenDescriptor $descriptor Описание токена
     * @param int $sort Порядок проверки токена лексером
     * @return $this
     */
    public function upsert(TokenDescriptor $descriptor, int $sort): static
    {
        $this->items[$descriptor->open] = new TokenCollectionItem($descriptor, $sort);
        return $this;
    }

    /**
     * Получение отсортированного списка описаний токенов
     *
     * По умолчанию список подготавливается(сортируется) при первом вызове. Далее возвращается тот же список.
     * Если передать true в качестве значения параметра то подготовка списка производится при этом вызове.
     * @param bool $reset Флаг безусловной подготовки списка
     * @return list<TokenDescriptor>
     */
    public function list(bool $reset = false): array
    {
        if ($reset || empty($this->descriptors)) {
            uasort($this->items, static fn(TokenCollectionItem $a, TokenCollectionItem $b) => $a->sort <=> $b->sort);
            $this->descriptors = [];
            foreach ($this->items as $item) {
                $this->descriptors[] = $item->descriptor;
            }
        }
        return $this->descriptors;
    }

    /**
     * Полная замена списка
     * @param list<TokenCollectionItem> $items
     * @return $this
     */
    public function reset(array $items): static
    {
        $this->items = [];
        $this->descriptors = [];
        foreach ($items as $item) {
            $this->items[$item->descriptor->open] = $item;
        }

        return $this;
    }
}