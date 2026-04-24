<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\EnableMouseCapture;
use PHPUnit\Framework\TestCase;

final class EnableMouseCaptureTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new EnableMouseCapture(true));
    }

    public function testExposesEnableFlag(): void
    {
        self::assertTrue((new EnableMouseCapture(true))->enable);
        self::assertFalse((new EnableMouseCapture(false))->enable);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('EnableMouseCapture(true)', (string) new EnableMouseCapture(true));
        self::assertSame('EnableMouseCapture(false)', (string) new EnableMouseCapture(false));
    }
}
