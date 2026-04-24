<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Writer;

use PhpTui\Term\Writer;
use PhpTui\Term\Writer\StringWriter;
use PHPUnit\Framework\TestCase;

final class StringWriterTest extends TestCase
{
    public function testImplementsWriterInterface(): void
    {
        self::assertInstanceOf(Writer::class, StringWriter::new());
    }

    public function testBufferStartsEmpty(): void
    {
        self::assertSame('', StringWriter::new()->toString());
    }

    public function testAccumulatesWrites(): void
    {
        $writer = StringWriter::new();
        $writer->write('hello');
        $writer->write(' ');
        $writer->write('world');

        self::assertSame('hello world', $writer->toString());
    }

    public function testWriteEmptyStringIsNoop(): void
    {
        $writer = StringWriter::new();
        $writer->write('abc');
        $writer->write('');

        self::assertSame('abc', $writer->toString());
    }
}
