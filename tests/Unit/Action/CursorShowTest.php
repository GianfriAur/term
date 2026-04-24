<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\CursorShow;
use PHPUnit\Framework\TestCase;

final class CursorShowTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new CursorShow(true));
    }

    public function testExposesShowFlag(): void
    {
        self::assertTrue((new CursorShow(true))->show);
        self::assertFalse((new CursorShow(false))->show);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('CursorShow(true)', (string) new CursorShow(true));
        self::assertSame('CursorShow(false)', (string) new CursorShow(false));
    }
}
