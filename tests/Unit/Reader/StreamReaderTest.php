<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Reader;

use PhpTui\Term\Reader;
use PhpTui\Term\Reader\StreamReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StreamReaderTest extends TestCase
{
    public function testTtyFactoryReturnsReaderInstance(): void
    {
        self::assertInstanceOf(Reader::class, StreamReader::tty());
    }

    public function testReadsBytesFromStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);
        fwrite($stream, 'hello');
        rewind($stream);

        $reader = $this->readerFromStream($stream);

        self::assertSame('hello', $reader->read());
    }

    public function testReturnsNullWhenStreamIsEmpty(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);

        $reader = $this->readerFromStream($stream);

        self::assertNull($reader->read());
    }

    public function testReturnsNullAfterStreamExhausted(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);
        fwrite($stream, 'data');
        rewind($stream);

        $reader = $this->readerFromStream($stream);

        self::assertSame('data', $reader->read());
        self::assertNull($reader->read());
    }

    /**
     * @param resource $stream
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function readerFromStream($stream): StreamReader
    {
        $reflection = new ReflectionClass(StreamReader::class);
        $reader = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('stream');
        $property->setValue($reader, $stream);

        return $reader;
    }
}
