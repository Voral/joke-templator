<?php

namespace Vasoft\Joke\Templator\Tests\Core\Container;

use PHPUnit\Framework\TestCase;
use Vasoft\Joke\Templator\Core\Container\TokenCollection;
use Vasoft\Joke\Templator\Core\Container\TokenCollectionItem;
use Vasoft\Joke\Templator\Core\Lexer\PrintToken;
use Vasoft\Joke\Templator\Core\Lexer\StatementToken;
use Vasoft\Joke\Templator\Core\Lexer\TokenDescriptor;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;

class TokenCollectionTest extends TestCase
{
    public function testAdd(): void
    {
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{{', '}}', PrintToken::class), 100);
        self::assertCount(1,$collection->list());
    }
    public function testAddTwice(): void
    {
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{{', '}}', PrintToken::class), 100);
        self::expectException(TemplatorException::class);
        self::expectExceptionMessage('Token already exists');
        $collection->add(new TokenDescriptor('{{', '}}', StatementToken::class), 100);
    }
    public function testAddSeveral(): void
    {
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{{', '}}', PrintToken::class), 100);
        $collection->add(new TokenDescriptor('{%', '%}', StatementToken::class), 100);
        self::assertCount(2,$collection->list());
    }

    public function testSorted(): void
    {
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{9', '}}', PrintToken::class), 9);
        $collection->add(new TokenDescriptor('{8a', '%}', StatementToken::class), 8);
        $collection->add(new TokenDescriptor('{8b', '%}', StatementToken::class), 8);
        $collection->add(new TokenDescriptor('{5', '%}', StatementToken::class), 5);
        $collection->add(new TokenDescriptor('{6', '%}', StatementToken::class), 6);
        $sorted = $collection->list();

        self::assertCount(5,$sorted);
        self::assertSame('{5',$sorted[0]->open);
        self::assertSame('{6',$sorted[1]->open);
        self::assertContains($sorted[2]->open,['{8a','{8b']);
        self::assertContains($sorted[3]->open,['{8a','{8b']);
        self::assertSame('{9',$sorted[4]->open);
    }
    public function testSortedOnceAndResetAndUpsert(): void
    {
        $descriptor = new TokenDescriptor('{5', '%}', StatementToken::class);
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{9', '}}', PrintToken::class), 9);
        $collection->add($descriptor, 5);

        $sorted = $collection->list();
        $collection->add(new TokenDescriptor('{1', '}}', PrintToken::class), 1);
        $collection->upsert($descriptor, 10);

        self::assertCount(2,$sorted);
        self::assertSame('{5',$sorted[0]->open);
        self::assertSame('{9',$sorted[1]->open);

        $sorted = $collection->list();
        self::assertCount(2,$sorted, 'Sorted twice');

        $sorted = $collection->list(true);
        self::assertCount(3,$sorted);
        self::assertSame('{1',$sorted[0]->open);
        self::assertSame('{9',$sorted[1]->open);
        self::assertSame('{5',$sorted[2]->open);
    }
    public function testReset(): void
    {
        $collection = new TokenCollection();
        $collection->add(new TokenDescriptor('{9', '}}', PrintToken::class), 9);
        $collection->add(new TokenDescriptor('{1', '}}', PrintToken::class), 1);
        $sorted = $collection->list();
        self::assertCount(2,$sorted);

        $collection->reset([
            new TokenCollectionItem(new TokenDescriptor('{12', '}}', PrintToken::class), 12),
            new TokenCollectionItem(new TokenDescriptor('{11', '}}', PrintToken::class), 11),
            new TokenCollectionItem(new TokenDescriptor('{15', '}}', PrintToken::class), 15),
        ]);

        $sorted = $collection->list();
        self::assertCount(3,$sorted);
        self::assertSame('{11',$sorted[0]->open);
        self::assertSame('{12',$sorted[1]->open);
        self::assertSame('{15',$sorted[2]->open);
    }

}
