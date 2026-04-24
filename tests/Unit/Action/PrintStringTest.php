<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\PrintString;
use PHPUnit\Framework\TestCase;

final class PrintStringTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new PrintString('hello'));
    }

    public function testExposesString(): void
    {
        self::assertSame('hello', (new PrintString('hello'))->string);
        self::assertSame('', (new PrintString(''))->string);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('Print("hello")', (string) new PrintString('hello'));
        self::assertSame('Print("")', (string) new PrintString(''));
    }
}
