<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Reader;

use PhpTui\Term\Reader;
use PhpTui\Term\Reader\ArrayReader;
use PHPUnit\Framework\TestCase;

final class ArrayReaderTest extends TestCase
{
    public function testImplementsReaderInterface(): void
    {
        self::assertInstanceOf(Reader::class, new ArrayReader([]));
    }

    public function testReturnsChunksInOrder(): void
    {
        $reader = new ArrayReader(['foo', 'bar', 'baz']);

        self::assertSame('foo', $reader->read());
        self::assertSame('bar', $reader->read());
        self::assertSame('baz', $reader->read());
    }

    public function testReturnsNullWhenExhausted(): void
    {
        $reader = new ArrayReader(['only']);

        self::assertSame('only', $reader->read());
        self::assertNull($reader->read());
        self::assertNull($reader->read());
    }

    public function testEmptyArrayReturnsNull(): void
    {
        self::assertNull((new ArrayReader([]))->read());
    }
}
