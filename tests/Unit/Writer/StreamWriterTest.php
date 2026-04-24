<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Writer;

use PhpTui\Term\Tests\Helpers\FailingStreamWrapper;
use PhpTui\Term\Writer;
use PhpTui\Term\Writer\StreamWriter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StreamWriterTest extends TestCase
{
    public function testStdoutFactoryReturnsWriterInstance(): void
    {
        self::assertInstanceOf(Writer::class, StreamWriter::stdout());
    }

    public function testWritesBytesToStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);

        $writer = $this->writerFromStream($stream);
        $writer->write('hello');

        rewind($stream);
        self::assertSame('hello', stream_get_contents($stream));
    }

    public function testAppendsOnMultipleWrites(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);

        $writer = $this->writerFromStream($stream);
        $writer->write('Foo');
        $writer->write('Bar');
        $writer->write('Baz');

        rewind($stream);
        self::assertSame('FooBarBaz', stream_get_contents($stream));
    }

    public function testEmptyWriteDoesNothing(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertNotFalse($stream);

        $writer = $this->writerFromStream($stream);
        $writer->write('');

        rewind($stream);
        self::assertSame('', stream_get_contents($stream));
    }

    public function testReturnsEarlyWhenFwriteFails(): void
    {
        FailingStreamWrapper::register();
        $stream = fopen(FailingStreamWrapper::PROTOCOL . '://test', 'w');
        self::assertNotFalse($stream);

        $writer = $this->writerFromStream($stream);

        // If the early return is missing, this would loop forever because
        // $written never advances when fwrite keeps returning false.
        $writer->write('anything');

        $this->addToAssertionCount(1);
    }

    /**
     * @param resource $stream
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function writerFromStream($stream): StreamWriter
    {
        $reflection = new ReflectionClass(StreamWriter::class);
        $writer = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('stream');
        $property->setValue($writer, $stream);

        return $writer;
    }
}
