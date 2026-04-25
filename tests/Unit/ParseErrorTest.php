<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit;

use PhpTui\Term\ParseError;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ParseErrorTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        self::assertInstanceOf(
            RuntimeException::class,
            ParseError::couldNotParseBuffer(['a']),
        );
    }

    public function testCouldNotParseOffsetMessage(): void
    {
        $error = ParseError::couldNotParseOffset(['a', 'b', 'c'], 1);

        self::assertSame(
            'Could not parse char "b" (offset 1) in ""abc""',
            $error->getMessage(),
        );
    }

    public function testCouldNotParseOffsetAppendsCustomMessage(): void
    {
        $error = ParseError::couldNotParseOffset(['x', 'y'], 0, 'unexpected byte');

        self::assertSame(
            'Could not parse char "x" (offset 0) in ""xy"": unexpected byte',
            $error->getMessage(),
        );
    }

    public function testCouldNotParseBufferDefaultMessage(): void
    {
        $error = ParseError::couldNotParseBuffer(['a', 'b']);

        self::assertSame('Could not parse buffer: "ab"', $error->getMessage());
    }

    public function testCouldNotParseBufferCustomMessage(): void
    {
        $error = ParseError::couldNotParseBuffer(['a', 'b'], 'bad escape');

        self::assertSame('bad escape: "ab"', $error->getMessage());
    }
}
