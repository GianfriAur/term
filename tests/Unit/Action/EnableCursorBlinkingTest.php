<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Unit\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Action\EnableCursorBlinking;
use PHPUnit\Framework\TestCase;

final class EnableCursorBlinkingTest extends TestCase
{
    public function testImplementsActionInterface(): void
    {
        self::assertInstanceOf(Action::class, new EnableCursorBlinking(true));
    }

    public function testExposesEnableFlag(): void
    {
        self::assertTrue((new EnableCursorBlinking(true))->enable);
        self::assertFalse((new EnableCursorBlinking(false))->enable);
    }

    public function testStringRepresentation(): void
    {
        self::assertSame('EnableCursorBlinking(true)', (string) new EnableCursorBlinking(true));
        self::assertSame('EnableCursorBlinking(false)', (string) new EnableCursorBlinking(false));
    }
}
